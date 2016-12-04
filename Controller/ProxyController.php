<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\Controller;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ProxyController
{
    use ContainerAwareTrait;

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

        $response = $client->send($request);
        if ($response->hasHeader('Transfer-Encoding')) {
            $response = $response->withoutHeader('Transfer-Encoding');
        }

        return $response;
    }
}
