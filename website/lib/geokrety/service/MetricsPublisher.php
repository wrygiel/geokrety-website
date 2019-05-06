<?php

namespace Geokrety\Service\Scheduled;

use Prometheus\Storage\InMemory;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\PushGateway;
use GKDB;

/**
 * MetricsPublisher : collect and publish geokrety website metrics
 */
class MetricsPublisher {
    private $pushGatewayHostPort;
    private $job = "MetricsPublisher";
    private $namespace = "geokrety";
    private $gatewayScrapIntervalSeconds = 15;

    private $registry;

    public function __construct($pushGatewayHostPort = 'pushgateway:9091') {
        $this->pushGatewayHostPort = $pushGatewayHostPort;
        $this->registry = null;
    }

    public function collect() {
        $this->registry = new CollectorRegistry(new InMemory());

        //~ ## health metric
        $nowMillisec = gettimeofday()["sec"];
        $healthGauge = $this->registry->registerGauge($this->namespace, "healthGauge", "metric visual health check", []);
        $healthGauge->set($nowMillisec, []);

        //~ ## errory metric
        $name = "errory";
        $help = "website actions logged into errory table";
        $erroryGauge = $this->registry->registerGauge($this->namespace, $name, $help, ['source', 'action', 'severity']);

        $link = GKDB::getLink();
        $sql =<<<EOSQL
            SELECT COUNT(id) as sum, uid, severity
            FROM (
                        SELECT id, uid, severity
                        FROM `gk-errory`
                        WHERE timestamp >= NOW() - INTERVAL $this->gatewayScrapIntervalSeconds SECOND
                  UNION
                        SELECT DISTINCT NULL, uid, severity FROM `gk-errory`
             ) ee
            GROUP BY uid,severity
EOSQL;

        $result = mysqli_query($link, $sql);
        while ($row = mysqli_fetch_array($result)) {
            list($errorCount, $errorUid, $errorSeverity) = $row;
            $erroryGauge->set($errorCount, ['errory', $errorUid, $errorSeverity]);
        }
    }
    public function render() {
        $renderer = new RenderTextFormat();
        $result = $renderer->render($this->registry->getMetricFamilySamples());
        return $result;
    }

    public function collectAndRender() {
        $this->collect();
        return $this->render();
    }

    public function publish() {
        $pushGateway = new PushGateway($this->pushGatewayHostPort);
        $pushGateway->push($this->registry, $this->job, array());
    }
}
