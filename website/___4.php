<?php
// Tweak some PHP configurations
// ini_set('memory_limit','1536M'); // 1.5 GB
// ini_set('max_execution_time', 18000); // 5 hours


require_once '__sentry.php';

use Gkm\Domain\GeoKrety;

$rollId = 0;
$file = "geokrety.xml";
$reader = new XMLReader();
$index = 0;

$useRedis = false; // 4..7 sec
$useRedis = true; // 15 sec

if ($useRedis) {
    $redis = new Redis();
    $redis->connect('redis', 6379);
    echo "Connection to server successfully<br/>\n";
}

function putInRedis($redis, $gkId, $geokretyObject) {
    $redisKey ="gkm-roll-$rollId-geogretyId-$gkId";
    $expireSec = 10;
    $redis->set($redisKey, $geokretyObject, $expireSec);
}

function xmlElementToGeokrety($xmlString) {
    $xmlGeokret = new SimpleXMLElement($xmlString);
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

$startMicroTime = microtime(true);

$reader->open($file);
while ($reader->read()) {
    if($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'geokret' ) {
        // For each node to type "geokret":
        $geokretXml = $reader->readOuterXml();
        $geokrety = xmlElementToGeokrety($geokretXml);
        if ($index == 0) {
            print_r($geokrety);
        }
        if ($index % 1000 == 0) {
            echo " * index $index<br/>\n";
        }
        if ($useRedis) {
            putInRedis($redis, $geokrety->id, $geokrety);
        }
        $index++;
    }
}

 $endMicroTime = microtime(true);
 $diff = $endMicroTime - $startMicroTime;
 echo "Execution time $diff seconds<br/>\n";
