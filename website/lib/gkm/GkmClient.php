<?php

namespace Gkm;


use Gkm\Domain\RestResponse;
use GuzzleHttp; // http://docs.guzzlephp.org/en/stable/
use GuzzleHttp\Psr7; // https://packagist.org/packages/guzzlehttp/psr7

/**
 * GkmClient : GKM API Client
 */
class GkmClient {
    private $gkmApiEndpoint;

    public function __construct($gkmApiEndpoint = 'https://api.geokretymap.org') {
        $this->gkmApiEndpoint = $gkmApiEndpoint;
    }

    public function getBasicGeokretyById($geokretyId) {
        $client = new GuzzleHttp\Client();
        return $client->request('GET', $this->gkmApiEndpoint.'/gk/'.$geokretyId);
    }

    public function getFullGeokretyById($geokretyId) {
        $client = new GuzzleHttp\Client();
        return $client->request('GET', $this->gkmApiEndpoint.'/gk/'.$geokretyId.'/details');
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
}
