<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\Controller;

use Bangpound\Bundle\GuzzleProxyBundle\Factory\HttpFoundationFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Psr7;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ProxyController
{
    use ContainerAwareTrait;

    public function __construct()
    {
        $this->httpResponseFactory = new HttpFoundationFactory();
    }

    /**
     * @param $endpoint
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function proxy($endpoint, ServerRequestInterface $request)
    {
        /** @var ClientInterface $client */
        $client = $this->container->get('bangpound_guzzle_proxy.client.'.$endpoint);

        $rel = $request->getAttribute('path');
        if ($request->getQueryParams()) {
            $rel .= '?'.Psr7\build_query($request->getQueryParams());
        }
        $rel = new Psr7\Uri($rel);

        $uri = $client->getConfig('base_url');
        $uri = new Psr7\Uri($uri);
        $uri = Psr7\Uri::resolve($uri, $rel);

        $request = Psr7\modify_request($request, array(
            'uri' => $uri,
        ));

        $response = $client->send($request, [
            'http_errors' => false,
            'stream' => true,
        ]);

        foreach ($response->getHeader('Set-Cookie') as $cookie) {
            $cookie = SetCookie::fromString($cookie);
            $cookie->setPath('/'.$path.$cookie->getPath());
        }

        return $this->httpResponseFactory->createResponse($response);
    }
}
