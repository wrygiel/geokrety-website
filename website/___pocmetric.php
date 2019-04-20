<?php
require_once '__sentry.php';

use Geokrety\Service\Scheduled\MetricsPublisher;

$gtwHOstPort = 'pushgateway:9091';
$publisher = new MetricsPublisher($gtwHOstPort);
$publisher->collect();
$publisher->publish();


/*  ---------------------

cf. =>  geokrety-scripts/geokret-website-metrics.php

apt-get update
apt-get install cron
crontab -e

# add theses lines
DB_HOSTNAME=db
DB_USERNAME=root
DB_PASSWORD=xxxxx
DB_NAME='geokrety-db'
*/
// */1 * * * * cd /opt/geokrety-scripts/metrics/ && echo ./geokret-website-metrics.php >> /var/log/cron.log && /usr/local/bin/php ./geokret-website-metrics.php >> /var/log/cron.log 2>&1
/*
service cron status
service cron start

cat /var/log/cron.log

*/