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
    private $exportUrl = "https://api.geokrety.org/basex/export/geokrety.xml";

    private $redisHost = "redis";
    private $redisPort = 6379;
    private $redisValueTimeToLiveSec= 60;// to customize

    private $rollId = 0;
    private $job = "GKMConsistecyCheck";
    private $gkm;// gkm api client

    private $redisConnection;

    public function __construct($config) {
        $this->initConfig($config, self::CONFIG_API_ENDPOINT, "apiEndpoint");
        $this->gkm = new Gkm();
        $this->redis = new \Redis();
    }

    public function run() {
        $runExecutionTime = new ExecutionTime();
        $runExecutionTime->start();
        $executionTime = new ExecutionTime();

        $this->redis->connect($this->redisHost, $this->redisPort);
        echo "Connection to REDIS $this->redisHost:$this->redisPort successfully<br/>\n";

        $this->downloadLastGkmExportIntoRedis($this->exportUrl);

        $batchSize = 50;
        $batchCount = 1;

        for ($i=0;$i<$batchCount;$i++) {

            $executionTime->start();
            $geokrets = $this->collectNextGeokretyToSync($batchSize);
            $geokretsCount = count($geokrets);
            $executionTime->end();

            echo "$i ) $geokretsCount geokrets<br/>" . $executionTime;

            foreach  ($geokrets as $geokrety) {
                $this->compareGeokretyWithRedis($geokrety);
            }

            // DEBUG // echo $this->objectToHtml($gkmGeokrets);
        }

        $runExecutionTime->end();
        echo "---<br/>\n";
        echo $runExecutionTime;
//         echo "TOTAL) $batchSize x $batchCount geokrety<br/>" . $runExecutionTime;
    }

    private function compareGeokretyWithRedis($geokretyObject) {
        $gkId = $geokretyObject["id"];
        $gkName = $geokretyObject["nazwa"];

        $gkmObject = $this->getFromRedis($gkId);
        $gkmName = $gkmObject["name"];

        // compare $geokretyObject from database /vs/ $gkmObject (redis cache) from last export
        if (!isset($gkmObject) || $gkmObject == null) {
            echo " X $gkId missing on GKM side<br/>\n";
            return;
        }

        if ($gkName != $gkmName) {
            echo " X $gkId not the same name($gkName) on GKM side($gkmName)<br/>\n";
            return;
        }
        echo " * $gkId OK<br/>\n";
    }

    private function xmlElementToGeokrety($xmlString) {
        $xmlGeokret = new \SimpleXMLElement($xmlString);
        $attributes = $xmlGeokret->attributes();
        return [
            "date" => (string) $attributes->date,
            "missing" => (string) $attributes->missing,
            "ownername" => (string) $attributes->ownername,
            "id" => (string) $attributes->id,
            "dist" => (string) $attributes->dist,
            "lat" => (float) $attributes->lat,
            "lon" => (float) $attributes->lon,
            "owner_id" => (string) $attributes->owner_id,
            "state" => (string) $attributes->state,
            "type" => (string) $attributes->type,
            "last_pos_id" => (string) $attributes->last_pos_id,
            "last_log_id" => (string) $attributes->last_log_id,
            "name" => (string) $xmlGeokret,
        ];
    }

    private function buildRedisKey($gkId) {
        $rollId = $this->rollId;
        return "gkm-roll-$rollId-gkid-$gkId";
    }

    private function getFromRedis($gkId) {
        $redisKey = $this->buildRedisKey($gkId);
        $jsonObject = $this->redis->get($redisKey);
        return json_decode($jsonObject, true);
    }

    private function putInRedis($gkId, $geokretyObject) {
        $redisKey = $this->buildRedisKey($gkId);
        $jsonObject = json_encode($geokretyObject);
        $this->redis->set($redisKey, $jsonObject, $this->redisValueTimeToLiveSec);
    }


    private function downloadLastGkmExportIntoRedis($resourceUrl) {
        $reader = new \XMLReader();
        $reader->open($resourceUrl);
        while ($reader->read()) {
            if($reader->nodeType == \XMLReader::ELEMENT && $reader->name == 'geokret' ) {
                // For each node to type "geokret":
                $geokretXml = $reader->readOuterXml();
                $geokrety = $this->xmlElementToGeokrety($geokretXml);
                $gkId = $geokrety["id"];

                if ($index % 1000 == 0) {
                    echo " * index $index<br/>\n";
                }
                $this->putInRedis($gkId, $geokrety);
                $index++;
            }
            // if ($this->debugMode && $index > 1000) break; // TODO
        }
    }


    private function collectNextGeokretyToSync($batchSize = 50) { // 30 SOMETIME OK // 50 RESULT IN 503
        $link = GKDB::getLink();
$sql = <<<EOQUERY
        SELECT    `id`,`nr`,`nazwa`,`owner`
        FROM      `gk-geokrety`
        ORDER BY timestamp ASC
        LIMIT $batchSize
EOQUERY;
// TODO DESC
        echo "$sql<br/>\n";
        if (!($stmt = $link->prepare($sql))) {
            throw new \Exception($action.' prepare failed: ('.$this->dblink->errno.') '.$this->dblink->error);
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
        $stmt->bind_result($gkId, $nr, $nazwa, $owner);

        while ($stmt->fetch()) {
            $geokret = [];
            // DEBUG // echo "$gkId<br/>\n";
            $geokret["id"] = $gkId;
            $geokret["nr"] = $nr;
            $geokret["nazwa"] = $nazwa;
            $geokret["owner"] = $owner;
            array_push($geokrets, $geokret);
        }

        $stmt->close();

        return $geokrets;
    }

    private function initConfig($config, $name, $attribute) {
        if (isset($config[$name])) {
            $this->$attribute = $config[$name];
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
