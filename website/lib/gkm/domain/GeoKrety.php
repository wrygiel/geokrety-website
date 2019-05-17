<?php

namespace Gkm\Domain;

class GeoKrety {
    public $id;
    public $dateMoved;
    public $ownerName;
    public $ownerId;
    public $distanceTraveledKm;
    public $waypointCode;
    public $state;
    public $typeId;
    public $positionLat;
    public $positionLon;
    public $imageSrc;
    public $imageTitle;
    public $name;
    public $lastMoveId;

    public function __construct($xmlBasicDocument = null) {
        if ($xmlBasicDocument == null) {
            return;
        }
        $loadedObject = simplexml_load_string($xmlBasicDocument);
        // DEBUG // var_dump($loadedObject);
        $geokret = $loadedObject->geokrety->geokret;
        $this->id = (string)$geokret->attributes()->id;
        $this->dateMoved = (string)$geokret->attributes()->date; // no time
        $this->ownerName = (string)$geokret->attributes()->ownername;
        $this->ownerId = (string)$geokret->attributes()->owner_id;
        $this->distanceTraveledKm = (int)$geokret->attributes()->dist;
        $this->waypointCode = (string)$geokret->attributes()->waypoint;
        $this->state = (string)$geokret->attributes()->state;
        $this->typeId = (string)$geokret->attributes()->type;
        $this->positionLat = (string)$geokret->attributes()->lat;
        $this->positionLon = (string)$geokret->attributes()->lon;
        $this->imageSrc = (string)$geokret->attributes()->image;
        // no image title
        $this->name = (string)$geokret;
        $this->lastMoveId= (string)$geokret->attributes()->last_log_id;
    }
/*
    public function __construct($xmlFullDocument) {
        $loadedObject = simplexml_load_string($xmlFullDocument);
        // DEBUG // var_dump($loadedObject);
        $geokret = $loadedObject->geokrety->geokret;
        $this->id = (string)$geokret->attributes()->id;
        $this->dateMoved = (string)$geokret->moves->move[0]->date->attributes()->moved;
        $this->ownerName = (string)$geokret->owner;
        $this->ownerId = (string)$geokret->owner->attributes()->id;
        $this->distanceTraveledKm = (int)$geokret->distancetraveled;
        $this->waypointCode = (string)$geokret->waypoint;
        $this->state = (string)$geokret->state;
        $this->typeId = (string)$geokret->type->attributes()->id;
        $this->positionLat = (string)$geokret->position->attributes()->latitude;
        $this->positionLon = (string)$geokret->position->attributes()->longitude;
        $this->imageSrc = (string)$geokret->image->attributes()->title;
        $this->imageTitle = (string)$geokret->image;
        $this->name = (string)$geokret->name;
        $this->lastMoveId= (string)$geokret->moves->move[0]->id;
    }
    */
}
