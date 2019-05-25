<?php
// Tweak some PHP configurations
ini_set('memory_limit','1536M'); // 1.5 GB
ini_set('max_execution_time', 18000); // 5 hours


require_once '__sentry.php';

use Gkm\Domain\GeoKrety;

$startMicroTime = microtime(true);
$file = "geokrety.xml";

 $arrayOfGeokrety = [];
 $loadedObject=simplexml_load_file($file);

 if (!isset($loadedObject->geokrety)) {
     echo "no gk";
 }

$i = 0;
 while (isset($loadedObject->geokrety->geokret[$i])) {
     $geokretObject = $loadedObject->geokrety->geokret[$i];
     $geokrety = new Geokrety();
     $geokrety->id = (string)$geokretObject->attributes()->id;
     $geokrety->dateMoved = (string)$geokretObject->attributes()->date; // no time
     $geokrety->ownerName = (string)$geokretObject->attributes()->ownername;
     $geokrety->ownerId = (string)$geokretObject->attributes()->owner_id;
     $geokrety->distanceTraveledKm = (int)$geokretObject->attributes()->dist;
     $geokrety->waypointCode = (string)$geokretObject->attributes()->waypoint;
     $geokrety->state = (string)$geokretObject->attributes()->state;
     $geokrety->typeId = (string)$geokretObject->attributes()->type;
     $geokrety->positionLat = (string)$geokretObject->attributes()->lat;
     $geokrety->positionLon = (string)$geokretObject->attributes()->lon;
     $geokrety->imageSrc = (string)$geokretObject->attributes()->image;
     // no image title
     $geokrety->name = (string)$geokretObject;
     $geokrety->lastMoveId= (string)$geokretObject->attributes()->last_log_id;
     array_push($arrayOfGeokrety, $geokrety);
     $i++;
 }
 echo count($arrayOfGeokrety)." geokrets loaded<br/>\n";
 print_r($arrayOfGeokrety[0]);

 $endMicroTime = microtime(true);
 $diff = $endMicroTime - $startMicroTime;
 echo "Execution time $diff seconds<br/>\n";
