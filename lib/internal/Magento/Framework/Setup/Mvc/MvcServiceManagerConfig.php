<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Mvc;

use Laminas\ServiceManager\ServiceManager;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\SharedEventManager;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\ModuleManager\ModuleManager;
use Laminas\ModuleManager\Listener\ServiceListener;

/**
 * Native ServiceManagerConfig.
 * Replicates the essential service management functionality while avoiding Laminas mvc dependencies.
 */
class MvcServiceManagerConfig
{
    /**
     * @var array
     */
    protected array $config;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // Core configuration
        $this->config = [
            'abstract_factories' => [],
            'aliases' => [
                'EventManagerInterface'            => EventManager::class,
                EventManagerInterface::class       => 'EventManager',
                ModuleManager::class               => 'ModuleManager',
                ServiceListener::class             => 'ServiceListener',
                SharedEventManager::class          => 'SharedEventManager',
                'SharedEventManagerInterface'      => 'SharedEventManager',
                SharedEventManagerInterface::class => 'SharedEventManager',
            ],
            'delegators' => [],
            'factories' => [
                'EventManager'            => function ($container) {
                    $sharedManager = $container->has('SharedEventManager') ?
                        $container->get('SharedEventManager') : null;
                    return new EventManager($sharedManager);
                },
                'ModuleManager'           => ModuleManagerFactory::class,
                'ServiceListener'         => ServiceListenerFactory::class,
            ],
            'lazy_services' => [],
            'initializers' => [],
            'invokables' => [],
            'services' => [],
            'shared' => [
                'EventManager' => false,
            ],
        ];

        // Add ServiceManager factory
        $this->config['factories']['ServiceManager'] = function ($container) {
            return $container;
        };

        // Add SharedEventManager factory
        $this->config['factories']['SharedEventManager'] = function () {
            return new SharedEventManager();
        };

        // Add EventManagerAware initializer
        $this->config['initializers']['EventManagerAwareInitializer'] = function ($container, $instance) {
            if (!$instance instanceof \Laminas\EventManager\EventManagerAwareInterface) {
                return;
            }
            $eventManager = $instance->getEventManager();
            // If the instance has an EM WITH an SEM composed, do nothing.
            if ($eventManager instanceof EventManagerInterface
                && $eventManager->getSharedManager() instanceof SharedEventManagerInterface
            ) {
                return;
            }
            $instance->setEventManager($container->get('EventManager'));
        };

        // Merge with provided config (this includes application.config.php service_manager config)
        if (!empty($config)) {
            $this->config = array_merge_recursive($this->config, $config);
        }
    }

    /**
     * Configure service manager
     *
     * @param ServiceManager $serviceManager
     * @return ServiceManager
     */
    public function configureServiceManager(ServiceManager $serviceManager): ServiceManager
    {
        // Add ServiceManager service reference
        $this->config['services'][ServiceManager::class] = $serviceManager;

        // Enable override during bootstrapping
        $serviceManager->setAllowOverride(true);
        $serviceManager->configure($this->config);

        // Disable override after configuration
        $serviceManager->setAllowOverride(false);

        return $serviceManager;
    }

    /**
     * Return all service configuration
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->config;
    }
}
