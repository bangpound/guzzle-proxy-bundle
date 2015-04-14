<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ProxyLoader.
 */
class ProxyLoader extends Loader
{
    /**
     * Loads a resource.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        $pattern = '/{path}';
        $defaults = array(
            '_controller' => 'bangpound_guzzle_proxy.client.'.$resource.':send',
            '_guzzle_proxy' => $resource,
        );
        $requirements = array(
            'path' => '.+?',
        );
        $route = new Route($pattern, $defaults, $requirements);
        $routes->add('_guzzle_proxy_'.$resource, $route);

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
