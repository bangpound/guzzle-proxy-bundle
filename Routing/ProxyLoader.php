<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ProxyLoader
 * @package Bangpound\Bundle\GuzzleProxyBundle\Routing
 */
class ProxyLoader implements LoaderInterface
{
    private $endpoints;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->endpoints = $config;
    }

    /**
     * Loads a resource.
     *
     * @param  mixed                                      $resource The resource
     * @param  string                                     $type     The resource type
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        $pattern = '/{endpoint}/{path}';
        $defaults = array(
            '_controller' => 'BangpoundGuzzleProxyBundle:Default:index',
        );
        $requirements = array(
            'endpoint' => implode('|', array_keys($this->endpoints)),
            'path' => '.*?',
        );
        $route = new Route($pattern, $defaults, $requirements);
        $routes->add('bangpound_guzzle_proxy_endpoint', $route);

        return $routes;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return 'guzzle_proxy' === $type;
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolverInterface A LoaderResolverInterface instance
     */
    public function getResolver()
    {
        // TODO: Implement getResolver() method.
    }

    /**
     * Sets the loader resolver.
     *
     * @param LoaderResolverInterface $resolver A LoaderResolverInterface instance
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        // TODO: Implement setResolver() method.
    }
}
