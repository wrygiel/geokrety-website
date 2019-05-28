<?php

namespace Consistency;

use GKDB; // to duplicate on move to dedicated repo (or create share lib)
use Gkm\Gkm;
use Gkm\Domain\GeokretyNotFoundException;

/**
 * GkmConsistencyCheck : analyse consistency between GeoKrety database and GeoKretyMap service
 */
class GkmConsistencyCheck {
    const CONFIG_API_ENDPOINT = 'gkm_api_endpoint';

    //~ config
    private $apiEndpoint = "https://api.geokretymap.org";

    //~ private
    private $rollId = 0;
    private $job = "GKMConsistencyCheck";
    private $gkm;// gkm api client

    private $currentTimestamp = null;

    private $redisConnection;

    public function __construct($config) {
        $this->initConfig($config, self::CONFIG_API_ENDPOINT, "apiEndpoint");
        $this->redis = RedisClient::getInstance($config);
        $this->gkmExportDownloader = new GkmExportDownloader($config);
        $this->gkm = new Gkm();// no more used
    }

    public function run() {
        $runExecutionTime = new ExecutionTime();
        $executionTime = new ExecutionTime();

        $runExecutionTime->start();

        $executionTime->start();
        $this->gkmExportDownloader->run($this->rollId);
        $executionTime->end();
        echo "--> download and put in redis $executionTime <br/>\n";


        $executionTime->start();
        $batchSize = 50;
        $batchCount = 10;
        $endOfTable = false;
        $geokretyCount = 0;
        $wrongGeokretyCount = 0;

        for ($i=0;!$endOfTable && $i<$batchCount;$i++) {

            $geokrets = $this->collectNextGeokretyToSync($batchSize);
            $geokretsCount = count($geokrets);
            $endOfTable = ($geokretsCount == 0);

            echo "$i ) $geokretsCount geokrets<br/>" . $executionTime;

            foreach  ($geokrets as $geokrety) {
                $geokretyCount++;
                $isValid = $this->compareGeokretyWithRedis($this->rollId, $geokrety);
                if (!$isValid) {
                  $wrongGeokretyCount++;
                }
            }
            flush();
            // DEBUG // echo $this->objectToHtml($gkmGeokrets);
        }
        $executionTime->end();
        echo "--> compare $geokretyCount geokrety ($wrongGeokretyCount are invalids) - $executionTime <br/>\n";


        $runExecutionTime->end();
        echo "---TOTAL---<br/>\n";
        echo $runExecutionTime;
//         echo "TOTAL) $batchSize x $batchCount geokrety<br/>" . $runExecutionTime;
    }

    private function compareGeokretyWithRedis($rollId, $geokretyObject) {
        $gkId = $geokretyObject["id"];
        $gkName = $geokretyObject["nazwa"];


        $gkmObject = $this->redis->getFromRedis($rollId, $gkId);
        $gkmName = $gkmObject["name"];

        // compare $geokretyObject from database /vs/ $gkmObject (redis cache) from last export
        if (!isset($gkmObject) || $gkmObject == null) {
            echo " X $gkId missing on GKM side<br/>\n";
            return false;
        }

        if ($gkName != $gkmName) {
            echo " X $gkId not the same name($gkName) on GKM side($gkmName)<br/>\n";
            return false;
        }
        echo " * $gkId OK<br/>\n";
        return true;
    }

    private function collectNextGeokretyToSync($batchSize = 50) { // 30 SOMETIME OK // 50 RESULT IN 503
        $link = GKDB::getLink();
$sql = <<<EOQUERY
        SELECT    `id`,`nr`,`nazwa`,`owner`,`timestamp`
        FROM      `gk-geokrety`
        ORDER BY timestamp DESC
        LIMIT $batchSize
EOQUERY;

        if ($this->currentTimestamp != null) {
$sql = <<<EOQUERY
        SELECT    `id`,`nr`,`nazwa`,`owner`,`timestamp`
        FROM      `gk-geokrety`
        WHERE timestamp < ?
        ORDER BY timestamp DESC
        LIMIT $batchSize
EOQUERY;
        }
        echo "$sql<br/>\n";

        if (!($stmt = $link->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
        }
        if ($this->currentTimestamp != null && !$stmt->bind_param('s', $this->currentTimestamp)) {
            throw new \Exception($action.' binding parameters failed: ('.$stmt->errno.') '.$stmt->error);
        }
        if (!$stmt->execute()) {
            throw new \Exception($action.' execute failed: ('.$stmt->errno.') '.$stmt->error);
        }
        $stmt->store_result();
        $nbRow = $stmt->num_rows;

        $geokrets = array();

        if ($nbRow == 0) {
            return $geokrets;
        }

        // associate result vars
        $stmt->bind_result($gkId, $nr, $nazwa, $owner, $timestamp);

        while ($stmt->fetch()) {
            $geokret = [];
            // DEBUG // echo "$gkId<br/>\n";
            $geokret["id"] = $gkId;
            $geokret["nr"] = $nr;
            $geokret["nazwa"] = $nazwa;
            $geokret["owner"] = $owner;
            $geokret["timestamp"] = $timestamp;
            $this->currentTimestamp = $timestamp;
            array_push($geokrets, $geokret);
        }

        $stmt->close();

        return $geokrets;
    }

    private function initConfig($config, $name, $attribute) {
        $this->config = $config;
        if (isset($config[$name])) {
            $this->$attribute = $config[$name];
        } else if (isset($_ENV[$name])) {
            $this->$attribute = $_ENV[$name];
        }
    }

    private function collectGKMGeokretyOneByOne($geokrets = []) {
        $gkmGeokrets = [];
        foreach ($geokrets as $geokrety ) {
            $gkId = $geokrety["id"];
            // DEBUG //  echo $gkId."<br/>\n";
            try {
              $gkmGeokrety = $geokrety = $this->gkm->getGeokretyById($gkId);
              array_push($gkmGeokrets, $gkmGeokrety);
            } catch (GeokretyNotFoundException $notFoundException) {
              $this->logMissingGkm($gkId);
            }
        }
        return $gkmGeokrets;
    }

    private function collectGKMGeokretyBulk($geokrets = []) {
        $gkmGeokrets = [];
        $idsOnly = [];
        foreach ($geokrets as $geokrety ) {
            array_push($idsOnly, $geokrety["id"]);
        }
        return $geokrety = $this->gkm->getGeokretyByIds($idsOnly);
    }

    private function logMissingGkm($geokretyId) {
      echo "missing geokrety id=$geokretyId on GKM side<br/>\n";
    }


    private function objectToHtml($var) {
       $rep = print_r($var, true);
       return '<pre>' . htmlentities($rep) . '</pre>';
    }

}
