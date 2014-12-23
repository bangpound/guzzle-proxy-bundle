<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\EventListener;

use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
            $event->setResponse(new StreamedResponse(function () use ($result) {
                $body = $result->getBody();
                while (!$body->eof()) {
                    echo $body->read(256);
                }
            }, $result->getStatusCode(), $result->getHeaders()));
        }
    }
}
