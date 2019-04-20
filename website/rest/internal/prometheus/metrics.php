<?php
/**
 * Metrics ReST Controller.
 */
require_once '../../../__sentry.php';

use Geokrety\Service\Scheduled\MetricsPublisher;

$publisher = new MetricsPublisher($gtwHOstPort);
echo $publisher->collectAndRender();