<?php

namespace GeoKrety\Controller;

use GeoKrety\HealthState;

class HealthCheck extends Base {
    public function get($f3) {
        $state = new HealthState();
        echo json_encode($state);
    }
}