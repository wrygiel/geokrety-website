<?php

namespace Gkm;

use GuzzleHttp;

/**
 * GkmClient : GKM API Client
 */
class GkmClient {
    private $gkmApiEndpoint;

    private $getGeokretyByIdPath = "/gk/";

    public function __construct($gkmApiEndpoint = 'https://api.geokretymap.org') {
        $this->gkmApiEndpoint = $gkmApiEndpoint;
    }

    public function getGeokretyById($geokretyId) {
        // http://docs.guzzlephp.org/en/stable/
        $apiUrl = $this->gkmApiEndpoint.$this->getGeokretyByIdPath.$geokretyId;
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', $apiUrl);
        echo $res->getStatusCode();
        // "200"
        echo $res->getHeader('content-type')[0];
        // 'application/json; charset=utf8'
        echo $res->getBody();
        // {"type":"User"...'
    }
}
