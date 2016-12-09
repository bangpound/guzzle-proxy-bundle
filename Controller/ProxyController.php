<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\Controller;

use Bangpound\Bundle\GuzzleProxyBundle\Factory\HttpFoundationFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class ProxyController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var HttpFoundationFactory
     */
    private $httpResponseFactory;

    public function __construct()
    {
        $this->httpResponseFactory = new HttpFoundationFactory();
    }

    /**
     * @param $endpoint
     * @param RequestInterface|ServerRequestInterface $request
     *
     * @return Response
     *
     * @throws ServiceUnavailableHttpException
     */
    public function proxy($endpoint, $path, ServerRequestInterface $request)
    {
        try {
            /** @var ClientInterface $client */
            $client = $this->container->get('bangpound_guzzle_proxy.client.'.$endpoint);
        } catch (ServiceCircularReferenceException $e) {
            throw new ServiceUnavailableHttpException();
        } catch (ServiceNotFoundException $e) {
            throw new ServiceUnavailableHttpException();
        }

        $rel = $path;
        if ($request->getQueryParams()) {
            $params = $request->getQueryParams();
            $rel .= '?'.Psr7\build_query($params);
        }
        $rel = new Psr7\Uri($rel);

        $uri = $client->getConfig('base_url');
        $uri = new Psr7\Uri($uri);
        $uri = Psr7\Uri::resolve($uri, $rel);

        $request = Psr7\modify_request($request, [
            'uri' => $uri,
        ]);

        try {
            $response = $client->send($request, [
            ]);
            $response = $response->withoutHeader('transfer-encoding');
            foreach ($response->getHeader('Set-Cookie') as $cookie) {
                $cookie = SetCookie::fromString($cookie);
                $cookie->setPath('/'.$path.$cookie->getPath());
            }

            return $this->httpResponseFactory->createResponse($response);
        } catch (GuzzleException $e) {
            throw new ServiceUnavailableHttpException();
        }
    }
}
