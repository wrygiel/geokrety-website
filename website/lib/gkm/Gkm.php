<?php

namespace Gkm;


use Gkm\GkmClient;
use Gkm\Domain\GeoKrety;
use Gkm\Domain\GeokretyNotFoundException;
use GuzzleHttp; // http://docs.guzzlephp.org/en/stable/
use GuzzleHttp\Psr7; // https://packagist.org/packages/guzzlehttp/psr7
use GuzzleHttp\Exception\ClientException; //  thrown for 400 level errors (cf quickstart)

/**
 * Gkm : GeoKretyMap
 */
class Gkm {
    private $client;

    public function __construct($gkmApiEndpoint = 'https://api.geokretymap.org') {
        $this->client = new GkmClient($gkmApiEndpoint);
    }

    public function getGeokretyById($geokretyId) {
        try {
            $response = $this->client->getBasicGeokretyById($geokretyId);
            if ($response->getStatusCode() != 200) {
                throw new GeokretyNotFoundException();
            }
            $responseBodyString = (string) $response->getBody();
            if (strpos($responseBodyString, '<geokrety/>') !== false) {
              throw new GeokretyNotFoundException();
            }
            $geokrety = new GeoKrety($responseBodyString);
            return $geokrety;
        } catch (ClientException $clientException) {
            if ($clientException->getResponse()->getStatusCode() == 404) {
                throw new GeokretyNotFoundException();
            }
            throw $clientException;
        }
    }
}