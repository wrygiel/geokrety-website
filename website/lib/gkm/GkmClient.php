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
        $response = $this->getGeokrety($this->gkmApiEndpoint.'/gk/'.$geokretyId);
        $geokrety = new GeoKrety($response->getBody());
        return $response;
    }

    public function getFullGeokretyById($geokretyId) {
        return $this->getGeokrety($this->gkmApiEndpoint.'/gk/'.$geokretyId.'/details');
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

    private function getGeokrety($url) {
        try {
            return $this->client->request('GET', $url);
        } catch (ClientException $clientException) {
            if ($clientException->getResponse()->getStatusCode() == 404) {
                throw new GeokretyNotFoundException();
            }
            throw $clientException;
        }
    }
}
