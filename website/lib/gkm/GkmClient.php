<?php

namespace Gkm;


use Gkm\Domain\GeoKrety;
use Gkm\Domain\GeokretyNotFoundException;
use GuzzleHttp; // http://docs.guzzlephp.org/en/stable/
use GuzzleHttp\Psr7; // https://packagist.org/packages/guzzlehttp/psr7
use GuzzleHttp\Exception\ClientException; //  thrown for 400 level errors (cf quickstart)

/**
 * GkmClient : GKM API Client
 */
class GkmClient {
    private $gkmApiEndpoint;
    private $client;

    public function __construct($gkmApiEndpoint = 'https://api.geokretymap.org') {
        $this->gkmApiEndpoint = $gkmApiEndpoint;
        $this->client = new GuzzleHttp\Client();
    }

    public function getBasicGeokretyById($geokretyId) {
        return $this->get($this->gkmApiEndpoint.'/gk/'.$geokretyId);
    }

    public function getFullGeokretyById($geokretyId) {
        return $this->get($this->gkmApiEndpoint.'/gk/'.$geokretyId.'/details');
    }

    public function debugShowResponse($description, $response) {
        $contentType = $response->getHeader('content-type')[0];
        $statusCode = $response->getStatusCode();
        return "<pre>\n$description ($statusCode - $contentType):\n".htmlspecialchars($response->getBody())."\n</pre>";
    }

    public function debugShowExceptionResponse($description, $clientException) {
        $response = $clientException->getResponse();
        $responseString = Psr7\str($response);

        $contentType = $response->getHeader('content-type')[0];
        $statusCode = $response->getStatusCode();
        $phrase= $response->getReasonPhrase();
        return "<pre>\n$description ($statusCode - $contentType): $phrase\n</pre>";
    }

    private function get($url) {
        // DEBUG // echo "GET : $url <br/>";
        return $this->client->request('GET', $url);
    }
}
