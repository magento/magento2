<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup;

use Zend\Mvc\Application as ZendApplication;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * This class is wrapper on \Zend\Mvc\Application and allows to do more customization like services loading, which
 * cannot be loaded via configuration.
 */
class Application
{
    /**
     * Creates \Zend\Mvc\Application and bootstrap it.
     * This method is similar to \Zend\Mvc\Application::init but allows to load
     * Magento specific services.
     *
     * @param array $configuration
     * @return ZendApplication
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
        $application = new ZendApplication($configuration, $serviceManager);
        $application->bootstrap($listeners);
        return $application;
    }

    /**
     * Uses \Zend\ServiceManager\ServiceManager::get method to load different kind of services.
     * Some services cannot be loaded via configuration like \Zend\ServiceManager\Di\DiAbstractServiceFactory and
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
