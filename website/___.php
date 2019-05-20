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

<pre>
SyncState: could be null or in temp cache file on disk
- rollId : current roll identifier, from 10_000 to 19_999. increment by 1 when timestamp is <= 0. move to rollId min value if out of range
- timestamp : define the current geokrety offset to use (geokrety creation date). When <= 0 need to restart from scratch (last created geokrety as first batch)


SyncParameters:
- job startup trigger (cron entry config)
- job max duration seconds
- batch size (geokrety select limit)
- roll min interval day


started by cron configuration a given job must finish the current roll in the limit of max duration
when the roll is finished, there's no more action to do
a roll is defined by the check of all geokrety table entries
we start by using current datetime and a select of X geokrety order by creation date desc
X is max batch size


if a job is triggered with an ended roll, a new roll is started if period between roll is reached
</pre>



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
