<?php

require_once '__sentry.php';

use Gkm\GkmClient;
use GuzzleHttp\Exception\ClientException; //  thrown for 400 level errors (cf quickstart)

$cgeoProbeGeokretyId = 46464;
$unknownGeokretyId = 145454545454545;

$gkmClient = new \Gkm\GkmClient();

$response = $gkmClient->getBasicGeokretyById($cgeoProbeGeokretyId);
echo $gkmClient->debugShowResponse("getBasicGeokretyById($cgeoProbeGeokretyId)", $response);

$response = $gkmClient->getFullGeokretyById($cgeoProbeGeokretyId);
echo $gkmClient->debugShowResponse("getFullGeokretyById($cgeoProbeGeokretyId)", $response);

$response = $gkmClient->getBasicGeokretyById($unknownGeokretyId);
echo $gkmClient->debugShowResponse("getBasicGeokretyById($unknownGeokretyId)", $response);

try {
    $response = $gkmClient->getFullGeokretyById($unknownGeokretyId);
    echo $gkmClient->debugShowResponse("getFullGeokretyById($unknownGeokretyId)", $response);
} catch (ClientException $clientException) {
    echo $gkmClient->debugShowExceptionResponse("getFullGeokretyById($unknownGeokretyId)", $clientException);
}
