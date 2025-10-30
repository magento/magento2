<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup;

use Magento\Framework\Setup\Mvc\MvcApplication as NativeApplication;
use Laminas\ServiceManager\ServiceManager;

/**
 * This class is wrapper on native Application
 *
 * It allows to do more customization like services loading, which
 * cannot be loaded via configuration.
 */
class Application
{
    /**
     * Creates native Application and bootstrap it.
     * This method is similar to native Application::init but allows to load
     * Magento specific services.
     *
     * @param array $configuration
     * @return NativeApplication
     */
    public function bootstrap(array $configuration)
    {
        $application = NativeApplication::init($configuration);

        // load specific services
        if (!empty($configuration['required_services'])) {
            $this->loadServices($application->getServiceManager(), $configuration['required_services']);
        }

        $listeners = $this->getListeners($application->getServiceManager(), $configuration);
        $application->bootstrap($listeners);
        return $application;
    }

    /**
     * Uses \Laminas\ServiceManager\ServiceManager::get method to load different kind of services.
     * Some services cannot be loaded via configuration like \Laminas\ServiceManager\Di\DiAbstractServiceFactory and
     * should be initialized via corresponding factory.
     *
     * @param ServiceManager $serviceManager
     * @param array $services
     * @return void
     */
    private function loadServices(ServiceManager $serviceManager, array $services)
    {
        foreach ($services as $serviceName) {
            $serviceManager->get($serviceName);
        }
    }

    /**
     * Gets list of application listeners.
     *
     * @param ServiceManager $serviceManager
     * @param array $configuration
     * @return array
     */
    private function getListeners(ServiceManager $serviceManager, array $configuration)
    {
        $appConfigListeners = isset($configuration['listeners']) ? $configuration['listeners'] : [];
        $config = $serviceManager->get('config');
        $serviceConfigListeners = isset($config['listeners']) ? $config['listeners'] : [];

        return array_unique(array_merge($serviceConfigListeners, $appConfigListeners));
    }
}
