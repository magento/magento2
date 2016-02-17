<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractPluginManagerFactory implements FactoryInterface
{
    const PLUGIN_MANAGER_CLASS = 'AbstractPluginManager';

    /**
     * Create and return a plugin manager.
     * Classes that extend this should provide a valid class for
     * the PLUGIN_MANGER_CLASS constant.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return AbstractPluginManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $pluginManagerClass = static::PLUGIN_MANAGER_CLASS;
        /* @var $plugins AbstractPluginManager */
        $plugins = new $pluginManagerClass;
        $plugins->setServiceLocator($serviceLocator);
        $configuration = $serviceLocator->get('Config');

        if (isset($configuration['di']) && $serviceLocator->has('Di')) {
            $plugins->addAbstractFactory($serviceLocator->get('DiAbstractServiceFactory'));
        }

        return $plugins;
    }
}
