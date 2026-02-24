<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Mvc;

use Laminas\ServiceManager\ServiceManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Stdlib\ArrayUtils;

/**
 * Native MVC Application that replicates Laminas\Mvc\Application functionality
 * but without MVC routing/view dependencies - only the core bootstrapping needed for setup
 */
class MvcApplication
{
    /**
     * @var ServiceManager
     */
    private ServiceManager $serviceManager;

    /**
     * Class constructor
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(
        ServiceManager $serviceManager
    ) {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Get service manager
     *
     * @return ServiceManager
     */
    public function getServiceManager(): ServiceManager
    {
        return $this->serviceManager;
    }

    /**
     * Get event manager (compatibility method for tests)
     *
     * @return EventManagerInterface
     */
    public function getEventManager(): EventManagerInterface
    {
        return $this->serviceManager->get('EventManager');
    }

    /**
     * Get configuration (compatibility method for tests)
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->serviceManager->get('config');
    }

    /**
     * Bootstrap the application
     *
     * @param array $listeners
     * @return self
     */
    public function bootstrap(array $listeners = []): static
    {
        // Bootstrap listeners for setup
        foreach ($listeners as $listener) {
            if (is_string($listener)) {
                $listenerInstance = $this->serviceManager->get($listener);
                if (method_exists($listenerInstance, 'onBootstrap')) {
                    // Create a compatible MvcEvent
                    $event = new MvcEvent();
                    $event->setApplication($this);
                    $listenerInstance->onBootstrap($event);
                }
            }
        }

        return $this;
    }

    /**
     * Initializes the application instance with services and configuration
     *
     * @param array $configuration
     * @return self
     */
    public static function init(array $configuration): MvcApplication
    {
        // Pre-load module configurations to get their service_manager configs
        $moduleConfigs = self::loadModuleConfigurations($configuration);

        // Merge application service_manager config with module service_manager configs
        $serviceManagerConfig = $configuration['service_manager'] ?? [];
        foreach ($moduleConfigs as $moduleConfig) {
            if (isset($moduleConfig['service_manager'])) {
                $serviceManagerConfig = ArrayUtils::merge($serviceManagerConfig, $moduleConfig['service_manager']);
            }
        }

        $smConfig = new MvcServiceManagerConfig($serviceManagerConfig);

        $serviceManager = new ServiceManager();
        $smConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $configuration);

        // Register ServiceManager instance for Magento's ObjectManager access
        ServiceManagerFactory::setServiceManager($serviceManager);

        // Load modules (now just for events, configs are already loaded)
        $moduleManager = $serviceManager->get('ModuleManager');
        $moduleManager->loadModules();

        // Load autoload configurations (global.php, local.php)
        self::loadAutoloadConfig($serviceManager, $configuration);

        return new self($serviceManager);
    }

    /**
     * Pre-load module configurations
     *
     * @param array $configuration
     * @return array
     */
    private static function loadModuleConfigurations(array $configuration): array
    {
        $moduleConfigs = [];
        $modules = $configuration['modules'] ?? [];

        // Load configuration from each module
        foreach ($modules as $moduleName) {
            if (class_exists($moduleName . '\Module')) {
                $moduleClass = $moduleName . '\Module';
                $moduleInstance = new $moduleClass();

                if (method_exists($moduleInstance, 'getConfig')) {
                    $moduleConfig = $moduleInstance->getConfig();
                    if (is_array($moduleConfig)) {
                        $moduleConfigs[] = $moduleConfig;
                    }
                }
            }
        }

        return $moduleConfigs;
    }

    /**
     * Load autoload configurations
     *
     * @param ServiceManager $serviceManager
     * @param array $configuration
     */
    private static function loadAutoloadConfig(ServiceManager $serviceManager, array $configuration): void
    {
        $mergedConfig = $configuration;

        // Load global.php and local.php configurations from config_glob_paths
        if (isset($configuration['module_listener_options']['config_glob_paths'])) {
            foreach ($configuration['module_listener_options']['config_glob_paths'] as $globPath) {
                $files = glob($globPath, GLOB_BRACE);
                foreach ($files as $file) {
                    if (is_readable($file)) {
                        $autoloadConfig = include $file;
                        if (is_array($autoloadConfig)) {
                            $mergedConfig = array_merge_recursive($mergedConfig, $autoloadConfig);
                        }
                    }
                }
            }
        }

        // Load Setup module configuration
        if (class_exists(\Magento\Setup\Module::class)) {
            $module = new \Magento\Setup\Module();
            $moduleConfig = $module->getConfig();
            $mergedConfig = array_merge_recursive($mergedConfig, $moduleConfig);
        }

        $serviceManager->setService('config', $mergedConfig);
    }
}
