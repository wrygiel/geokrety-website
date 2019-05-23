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
    private $apiEndpoint = 'https://api.geokretymap.org';

    private $job = "GKMConsistecyCheck";
    private $gkm;

    public function __construct($config) {
        $this->initConfig($config, self::CONFIG_API_ENDPOINT, "apiEndpoint");
        $this->gkm = new Gkm();
    }

    public function run() {
        $runExecutionTime = new ExecutionTime();
        $runExecutionTime->start();

        $executionTime = new ExecutionTime();

        $batchSize = 50;
        $batchCount = 10;

        for ($i=0;$i<$batchCount;$i++) {

            $executionTime->start();
            $geokrets = $this->collectNextGeokretyToSync($batchSize);
            $geokretsCount = count($geokrets);
            $executionTime->end();

            echo "$i ) $geokretsCount geokrets<br/>" . $executionTime;

            $executionTime->start();
            $gkmGeokrets = $this->collectGKMGeokretyBulk($geokrets);
            $gkmGeokretsCount = count($gkmGeokrets);
            $executionTime->end();

            echo "$i ) $gkmGeokretsCount gkmGeokrets (Bulk)<br/>" . $executionTime;
            echo "-<br/>\n";

            // DEBUG // echo $this->objectToHtml($gkmGeokrets);
        }
        $runExecutionTime->end();
        echo "---<br/>\n";
        echo "TOTAL) $batchSize x $batchCount geokrety<br/>" . $runExecutionTime;
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
