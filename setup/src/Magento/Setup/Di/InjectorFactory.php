<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Di;

use Laminas\Di\ConfigInterface;
use Laminas\Di\Definition\RuntimeDefinition;
use Laminas\Di\Injector;
use Laminas\Di\InjectorInterface;
use Psr\Container\ContainerInterface;
use Zend\Di\ConfigInterface as LegacyConfigInterace;
use Laminas\Di\Container\ConfigFactory;

/**
 * Implements the DependencyInjector service factory for laminas-servicemanager
 */
class InjectorFactory
{
    private function createConfig(ContainerInterface $container): ConfigInterface
    {
        if ($container->has(ConfigInterface::class)) {
            return $container->get(ConfigInterface::class);
        }

        if ($container->has(LegacyConfigInterace::class)) {
            return $container->get(LegacyConfigInterace::class);
        }

        return (new ConfigFactory())->create($container);
    }

    /**
     * {@inheritDoc}
     */
    public function create(ContainerInterface $container): InjectorInterface
    {
        $config = $this->createConfig($container);
        $definition = new RuntimeDefinition();
        return new Injector(
            $config,
            $container,
            $definition,
            new DependencyResolver($definition, $config)
        );
    }

    /**
     * Make the instance invokable
     */
    public function __invoke(ContainerInterface $container): InjectorInterface
    {
        return $this->create($container);
    }
}
