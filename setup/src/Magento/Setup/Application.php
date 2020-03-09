<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup;

use Laminas\Mvc\Application as LaminasApplication;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

/**
 * This class is wrapper on \Laminas\Mvc\Application
 *
 * It allows to do more customization like services loading, which
 * cannot be loaded via configuration.
 */
class Application
{
    /**
     * Creates \Laminas\Mvc\Application and bootstrap it.
     * This method is similar to \Laminas\Mvc\Application::init but allows to load
     * Magento specific services.
     *
     * @param array $configuration
     * @return LaminasApplication
     */
    public function bootstrap(array $configuration)
    {
        $managerConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : [];
        $managerConfig = new ServiceManagerConfig($managerConfig);

        $serviceManager = new ServiceManager();
        $managerConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $configuration);

        $serviceManager->get('ModuleManager')->loadModules();

        // load specific services
        if (!empty($configuration['required_services'])) {
            $this->loadServices($serviceManager, $configuration['required_services']);
        }

        $listeners = $this->getListeners($serviceManager, $configuration);
        $application = new LaminasApplication(
            $configuration,
            $serviceManager,
            $serviceManager->get('EventManager'),
            $serviceManager->get('Request'),
            $serviceManager->get('Response')
        );
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
