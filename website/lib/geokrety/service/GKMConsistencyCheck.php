<?php

namespace Geokrety\Service;

use GKDB;

/**
 * GKMConsistencyCheck : analyse consistency between GeoKrety database and GeoKretyMap service
 */
class GKMConsistencyCheck {
    private $gkmHostPort;
    private $job = "GKMConsistecyCheck";

    public function __construct($gkmHostPort = 'gkm:80') {
        $this->gkmHostPort = $gkmHostPort;
    }

}
