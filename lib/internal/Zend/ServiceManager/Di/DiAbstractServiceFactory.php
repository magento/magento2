<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_ServiceManager
 */

namespace Zend\ServiceManager\Di;

use Zend\Di\Di;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DiAbstractServiceFactory extends DiServiceFactory implements AbstractFactoryInterface
{
    /**
     * Constructor
     *
     * @param \Zend\Di\Di $di
     * @param null|string|\Zend\Di\InstanceManager $useServiceLocator
     */
    public function __construct(Di $di, $useServiceLocator = self::USE_SL_NONE)
    {
        $this->di = $di;
        if (in_array($useServiceLocator, array(self::USE_SL_BEFORE_DI, self::USE_SL_AFTER_DI, self::USE_SL_NONE))) {
            $this->useServiceLocator = $useServiceLocator;
        }

        // since we are using this in a proxy-fashion, localize state
        $this->definitions = $this->di->definitions;
        $this->instanceManager = $this->di->instanceManager;
    }

    /**
     * {@inheritDoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $serviceName, $requestedName)
    {
        $this->serviceLocator = $serviceLocator;
        if ($requestedName) {
            return $this->get($requestedName, array(), true);
        } else {
            return $this->get($serviceName, array(), true);
        }

    }

    /**
     * {@inheritDoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->instanceManager->hasSharedInstance($requestedName)
            || $this->instanceManager->hasAlias($requestedName)
            || $this->instanceManager->hasConfig($requestedName)
            || $this->instanceManager->hasTypePreferences($requestedName)
            || $this->definitions->hasClass($requestedName);
    }
}
