<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GuzzleController
 * @package Bangpound\Bundle\GuzzleProxyBundle\Controller
 */
class GuzzleController
{
    /**
     * @param  Request  $request
     * @param $endpoint
     * @param $path
     * @return Response
     */
    public function proxyAction(Request $request, $endpoint, $path)
    {
        // URL of the proxied service is extracted from the options. The requested path
        // and query string are attached.
        $url = Url::fromString($endpoint);
        $url->addPath($path);
        if ($request->server->has('QUERY_STRING')) {
            $url->setQuery($request->getQueryString(), true);
        }

        $client = new Client();
        $httpRequest = $client->createRequest($request->getMethod(), $url, array(
            'body' => $request->getContent(),
        ));
        try {
            $httpResponse = $client->send($httpRequest);
        } catch (BadResponseException $e) {
            $httpResponse = $e->getResponse();
        }

        // Stash the prepared Guzzle request and response in the Symfony request attributes
        // for debugging.
        $request->attributes->set('guzzle_request', $httpRequest);
        $request->attributes->set('guzzle_response', $httpResponse);

        $body = $httpResponse->getBody();
        $statusCode = $httpResponse->getStatusCode();

        // This cannot handle every response. Chunked transfer encoding would necessitate
        // a streaming response.
        $headers = $httpResponse->getHeaders();
        unset($headers['Transfer-Encoding']);

        return new Response($body, $statusCode, $headers);
    }
}
