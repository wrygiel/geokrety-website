<?php
/**
 * Metrics ReST Controller.
 */
require_once '../../../__sentry.php';

use Geokrety\Service\Scheduled\MetricsPublisher;

$publisher = new MetricsPublisher(PROMETHEUS_WEBSITE_SCRAPE_INTERVAL);
echo $publisher->collectAndRender();