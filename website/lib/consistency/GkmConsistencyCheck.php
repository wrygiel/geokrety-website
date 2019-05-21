<?php

namespace Consistency;

use GKDB; // to duplicate on move to dedicated repo (or create share lib)

/**
 * GkmConsistencyCheck : analyse consistency between GeoKrety database and GeoKretyMap service
 */
class GkmConsistencyCheck {
    const CONFIG_API_ENDPOINT = 'gkm_api_endpoint';


    //~ config
    private $apiEndpoint = 'https://api.geokretymap.org';

    private $job = "GKMConsistecyCheck";

    public function __construct($config) {
        $this->initConfig($config, self::CONFIG_API_ENDPOINT, "apiEndpoint");
    }

    public function run() {
        $geokrety = $this->collectNextGeokretyToSync();
        return $geokrety;
    }

    public function collectNextGeokretyToSync($batchSize = 50) {
        $link = GKDB::getLink();
$sql = <<<EOQUERY
        SELECT    `id`,`nr`,`nazwa`,`owner`
        FROM      `gk-geokrety`
        ORDER BY timestamp DESC
        LIMIT $batchSize
EOQUERY;
        echo $sql;
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

}
