<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\EventListener;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ControllerListener
{
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $endpoint = $request->attributes->get('_guzzle_proxy', false);

        if ($endpoint) {
            $client = $this->container->get('bangpound_guzzle_proxy.client.'.$endpoint);
            $httpRequest = $client->createRequest(
              $request->getMethod(),
              $request->attributes->get('path'),
              array(
                'query' => $request->query->all(),
                'body' => $request->getContent(true),
              )
            );

            $request->attributes->set('request', $httpRequest);
        }
    }
}
