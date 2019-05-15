<?php

require_once '__sentry.php';

use \Gkm\GkmClient;

$gkmClient = new \Gkm\GkmClient();
$gkmClient->getGeokretyById(46464);