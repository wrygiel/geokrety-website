<?php
// #################
// # requirement
// # - docker compose - redis service
// # - composer update
// https://stackoverflow.com/questions/31369867/how-to-install-php-redis-extension-using-the-official-php-docker-image-approach
// # docker sh:
// #  $ pecl install -o -f redis && docker-php-ext-enable redis
//
// # ini_set('memory_limit', '-1');

// FAILED > memory issue
//

// provided sample
require '__sentry.php';
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;

$adapter = 'redis'; // '$_GET['adapter'];
$redisHost = 'redis'; // isset($_SERVER['REDIS_HOST']) ? $_SERVER['REDIS_HOST'] : '127.0.0.1';
if ($adapter === 'redis') {
    Redis::setDefaultOptions(array('host' => $redisHost));
    $adapter = new Prometheus\Storage\Redis();
} elseif ($adapter === 'apc') {
    $adapter = new Prometheus\Storage\APC();
} elseif ($adapter === 'in-memory') {
    $adapter = new Prometheus\Storage\InMemory();
}
$registry = new CollectorRegistry($adapter);
$renderer = new RenderTextFormat();
$result = $renderer->render($registry->getMetricFamilySamples());
header('Content-type: ' . RenderTextFormat::MIME_TYPE);
echo $result;