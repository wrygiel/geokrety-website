<?php

namespace GeoKrety\Controller\Cli;

use GeoKrety\Controller\Cli\Traits\Script;
use GeoKrety\Model\EmailActivationToken as EmailActivationTokenModel;

class EmailActivationToken {
    use Script;

    public function prune(\Base $f3) {
        $this->start(__METHOD__);
        EmailActivationTokenModel::expireOldTokens();
        $this->end();
    }
}
