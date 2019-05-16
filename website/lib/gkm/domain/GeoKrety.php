<?php

namespace Gkm\Domain;

class GeoKrety {
    public $id;
    public $dateMoved;
    public $ownerName;
    public $ownerId;
    public $distanceTraveledKm;
    public $waypoint;
    public $state;
    public $type;
    public $lastPositionId;
    public $lastPositionLat;
    public $lastPositionLon;
    public $image;
    public $name;

    public function __construct($xmlDocument) {
        $loadedObject = simplexml_load_string($xmlDocument);
        $loadedGeokrety = $loadedObject->geokrety->geokret;
//        var_dump($loadedGeokrety);
        $this->dateMoved = (string)$loadedGeokrety[0]->attributes()->date;
//        var_dump($this);
// TBC...
    }
}
