<?php
// Tweak some PHP configurations
ini_set('memory_limit','1536M'); // 1.5 GB
ini_set('max_execution_time', 18000); // 5 hours


require_once '__sentry.php';

use Gkm\Domain\GeoKrety;

$gkId = 1234;
$startMicroTime = microtime(true);
$file = "geokrety.xml";

$reader = new XMLReader();
$reader->open($file);

$found = false;

while ($reader->read() && !$found) {
    if($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'geokret' ) {
        // For each node to type "geokret":
        $xmlGeokret = new SimpleXMLElement($reader->readOuterXml());
        $attributes = $xmlGeokret->attributes();
        if ($attributes->id == $gkId) {
          echo "ID: " . $attributes->id .
               ", ownername: " . $attributes->ownername . "\n";
           $found = true;
        }
    }
}





 $endMicroTime = microtime(true);
 $diff = $endMicroTime - $startMicroTime;
 echo "Execution time $diff seconds<br/>\n";
