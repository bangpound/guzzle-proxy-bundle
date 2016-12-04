<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\Factory;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HttpFoundationFactory extends \Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory
{
    /**
     * @param ResponseInterface $psrResponse
     *
     * @return StreamedResponse
     */
    public function createResponse(ResponseInterface $psrResponse)
    {
        /** @var StreamInterface $stream */
        $stream = $psrResponse->getBody();
        $callback = function () use ($stream) {
            while (!$stream->eof()) {
                echo $stream->read($stream->getMetadata('unread_bytes') ?: 128);
                flush();
            }
            $stream->close();
        };

        $response = new StreamedResponse(
            $callback,
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders()
        );
        $response->setProtocolVersion($psrResponse->getProtocolVersion());

        foreach ($psrResponse->getHeader('Set-Cookie') as $cookie) {
            $response->headers->setCookie($this->createCookie($cookie));
        }

        return $response;
    }

    /**
     * @param string $cookie
     *
     * @return Cookie
     */
    private function createCookie($cookie)
    {
        foreach (explode(';', $cookie) as $part) {
            $part = trim($part);

            $data = explode('=', $part, 2);
            $name = $data[0];
            $value = isset($data[1]) ? trim($data[1], " \n\r\t\0\x0B\"") : null;

            if (!isset($cookieName)) {
                $cookieName = $name;
                $cookieValue = $value;

                continue;
            }

            if ('expires' === strtolower($name) && null !== $value) {
                $cookieExpire = new \DateTime($value);

                continue;
            }

            if ('path' === strtolower($name) && null !== $value) {
                $cookiePath = $value;

                continue;
            }

            if ('domain' === strtolower($name) && null !== $value) {
                $cookieDomain = $value;

                continue;
            }

            if ('secure' === strtolower($name)) {
                $cookieSecure = true;

                continue;
            }

            if ('httponly' === strtolower($name)) {
                $cookieHttpOnly = true;

                continue;
            }
        }

        if (!isset($cookieName)) {
            throw new \InvalidArgumentException('The value of the Set-Cookie header is malformed.');
        }

        return new Cookie(
            $cookieName,
            $cookieValue,
            isset($cookieExpire) ? $cookieExpire : 0,
            isset($cookiePath) ? $cookiePath : '/',
            isset($cookieDomain) ? $cookieDomain : null,
            isset($cookieSecure),
            isset($cookieHttpOnly)
        );
    }
}
