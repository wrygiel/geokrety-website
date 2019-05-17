<?php

require_once '__sentry.php';

use Gkm\Gkm;
use Gkm\GkmClient;
use Gkm\Domain\GeokretyNotFoundException;

$cgeoProbeGeokretyId = 46464;
$anotherGeokretyId = 69469;
$unknownGeokretyId = 145454545454545;

$gkmClient = new GkmClient();
$gkm = new Gkm();

$geokrets = [$anotherGeokretyId, $unknownGeokretyId];
$geokrets = [$cgeoProbeGeokretyId, $unknownGeokretyId];


function objectToHtml($var) {
   $rep = print_r($var, true);
   return '<pre>' . htmlentities($rep) . '</pre>';
}

?>
<div style="float:right">
 <a href="#Todo">Todo</a> - <a href="#GkmClient">GkmClient</a>  - <a href="#Gkm">Gkm</a>
</div>

<h2>Todo</h2>
<ul>
 <li>create unit tests (mock http client?)</li>
 <li>create integration tests : env var to enable them</li>
 <li>move GkmClient into a create dedicated repository : geokrety/gkmClient</li>
 <li>use home-made template to define fields to use as comparator</li>
 <li>first version append to a file geokrety that are not sync</li>
 <li>improved version that append to a redis queue or database geokrety that are not sync</li>
 <li>create another job that take this entries to trigger gkm/.../dirty api cf issue #324
</ul>




<h2>GkmClient</h2>
<?php

foreach ($geokrets as &$geokretyId) {
    $action = "<h4>getBasicGeokretyById($geokretyId)</h6>";
    try {
        $response = $gkmClient->getBasicGeokretyById($geokretyId);
        echo $gkmClient->debugShowResponse($action, $response);
    } catch (GeokretyNotFoundException $notFoundException) {
        echo "$action: not found";
    } catch (ClientException $clientException) {
        echo $gkmClient->debugShowExceptionResponse($action, $clientException);
    }
    $action = "<h4>getFullGeokretyById($geokretyId)</h6>";
    try {
        $response = $gkmClient->getFullGeokretyById($geokretyId);
        echo $gkmClient->debugShowResponse($action, $response);
    } catch (GuzzleHttp\Exception\ClientException $clientException) {
        echo $gkmClient->debugShowExceptionResponse($action, $clientException);
    }
}

?>

<h2>Gkm</h2>
<?php

foreach ($geokrets as &$geokretyId) {
    echo "<h4>getGeokretyById($geokretyId)</h4>";
    try {
        $geokrety = $gkm->getGeokretyById($geokretyId);
        echo objectToHtml($geokrety);
    } catch (GeokretyNotFoundException $notFoundException) {
        echo "geokrety not found";
    }
}
