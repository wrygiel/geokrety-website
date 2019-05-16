TODO List:<br/>
<ul>
 <li>create unit tests (mock http client?)</li>
 <li>create integration tests : env var to enable them</li>
 <li>move GkmClient into a create dedicated repository : geokrety/gkmClient</li>
 <li>use home-made template to define fields to use as comparator</li>
 <li>first version append to a file geokrety that are not sync</li>
 <li>improved version that append to a redis queue or database geokrety that are not sync</li>
 <li>create another job that take this entries to trigger gkm/.../dirty api cf issue #324
</ul>

<?php

require_once '__sentry.php';

use Gkm\GkmClient;
use Gkm\Domain\GeokretyNotFoundException;


$cgeoProbeGeokretyId = 46464;
$unknownGeokretyId = 145454545454545;

$gkmClient = new \Gkm\GkmClient();

$geokrets = [$cgeoProbeGeokretyId, $unknownGeokretyId];

foreach ($geokrets as &$geokretyId) {
    echo $geokretyId;
    $action = "getBasicGeokretyById($geokretyId)";
    try {
        $response = $gkmClient->getBasicGeokretyById($geokretyId);
        echo $gkmClient->debugShowResponse($action, $response);
    } catch (GeokretyNotFoundException $notFoundException) {
        echo "$action: not found";
    } catch (ClientException $clientException) {
        echo $gkmClient->debugShowExceptionResponse($action, $clientException);
    }

    $action = "getFullGeokretyById($geokretyId)";
    try {
        $response = $gkmClient->getFullGeokretyById($geokretyId);
        echo $gkmClient->debugShowResponse($action, $response);
    } catch (GeokretyNotFoundException $notFoundException) {
        echo "$action: not found";
    } catch (ClientException $clientException) {
        echo $gkmClient->debugShowExceptionResponse($action, $clientException);
    }
}

