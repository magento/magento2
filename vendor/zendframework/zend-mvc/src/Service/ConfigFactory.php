<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigFactory implements FactoryInterface
{
    /**
     * Create the application configuration service
     *
     * Retrieves the Module Manager from the service locator, and executes
     * {@link Zend\ModuleManager\ModuleManager::loadModules()}.
     *
     * It then retrieves the config listener from the module manager, and from
     * that the merged configuration.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return array|\Traversable
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mm           = $serviceLocator->get('ModuleManager');
        $mm->loadModules();
        $moduleParams = $mm->getEvent()->getParams();
        $config       = $moduleParams['configListener']->getMergedConfig(false);
        return $config;
    }
}
