<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\EventListener;

use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class ResponseListener
{
    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();

        if ($result instanceof ResponseInterface) {
            $event->setResponse(new Response((string) $result->getBody(), $result->getStatusCode(), $result->getHeaders()));
        }
    }
}
