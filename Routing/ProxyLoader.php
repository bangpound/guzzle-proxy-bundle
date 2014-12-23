<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ProxyLoader
 * @package Bangpound\Bundle\GuzzleProxyBundle\Routing
 */
class ProxyLoader extends Loader
{
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
}
