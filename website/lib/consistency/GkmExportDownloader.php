<?php

namespace Consistency;

/**
 * GkmExportDownloader : download GeoKretyMap export into redis
 */
class GkmExportDownloader {
    const CONFIG_GKM_EXPORT_BASIC = 'gkm_export_basic';
    private $exportUrl = "https://api.geokrety.org/basex/export/geokrety.xml";

    private $redisValueTimeToLiveSec= 60;// TODO add to config

    public function __construct($config) {
        $this->initConfig($config, self::CONFIG_GKM_EXPORT_BASIC, "exportUrl");
        $this->redis = RedisClient::getInstance($config);
        $this->redis->connect();
    }

    public function run($rollId) {
        $reader = new \XMLReader();
        $reader->open($this->exportUrl);
        $index = 0;
        while ($reader->read()) {
            if($reader->nodeType == \XMLReader::ELEMENT && $reader->name == 'geokret' ) {
                // For each node to type "geokret":
                $geokretXml = $reader->readOuterXml();
                $geokrety = $this->xmlElementToGeokrety($geokretXml);
                $gkId = $geokrety["id"];
                if ($index > 0 && $index % 5000 == 0) {
                    echo " * #$rollId index $index<br/>\n";
                    flush();
                }
                $this->redis->putInRedis($rollId, $gkId, $geokrety, $this->redisValueTimeToLiveSec);
                $index++;
            }
        }
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

    private function initConfig($config, $name, $attribute) {
        $this->config = $config;
        if (isset($config[$name])) {
            $this->$attribute = $config[$name];
        } else if (isset($_ENV[$name])) {
            $this->$attribute = $_ENV[$name];
        }
    }

}