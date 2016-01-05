<?php

namespace Bangpound\Bundle\GuzzleProxyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BangpoundGuzzleProxyExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $baseConfig = $container->getDefinition('bangpound_guzzle_proxy.client')->getArgument(0);
        foreach ($config['endpoints'] as $name => $clientConfig) {
            $definition = new DefinitionDecorator('bangpound_guzzle_proxy.client');
            $definition->replaceArgument(0, array_merge($baseConfig, $clientConfig));
            $container->setDefinition('bangpound_guzzle_proxy.client.'.$name, $definition);
        }
    }
}
