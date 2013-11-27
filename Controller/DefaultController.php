<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package Bangpound\Bundle\GuzzleProxyBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @param $endpoint
     * @param $path
     * @return Response
     */
    public function indexAction(Request $request, $endpoint, $path)
    {
        $endpoint = $this->container->getParameter('bangpound_guzzle_proxy.endpoints')[$endpoint];

        // URL of the proxied service is extracted from the options. The requested path
        // and query string are attached.
        $url = Url::factory($endpoint['host']);
        $url->addPath($path)
            ->setQuery($request->getQueryString());

        $client = new Client();
        $httpRequest = $client->createRequest($request->getMethod(), $url, null, $request->getContent());
        try {
            $httpResponse = $httpRequest->send();
        } catch (BadResponseException $e) {
            $httpResponse = $e->getResponse();
        }

        // Stash the prepared Guzzle request and response in the Symfony request attributes
        // for debugging.
        $request->attributes->set('guzzle_request', $httpRequest);
        $request->attributes->set('guzzle_response', $httpResponse);

        $body = $httpResponse->getBody(true);
        $statusCode = $httpResponse->getStatusCode();

        // This cannot handle every response. Chunked transfer encoding would necessitate
        // a streaming response.
        $headers = $httpResponse->getHeaders()->toArray();
        unset($headers['Transfer-Encoding']);

        return new Response($body, $statusCode, $headers);
    }
}
