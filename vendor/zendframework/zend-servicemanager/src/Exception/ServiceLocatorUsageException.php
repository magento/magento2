<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager\Exception;

use Exception as BaseException;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceLocatorUsageException extends ServiceNotFoundException
{
    /**
     * Static constructor
     *
     * @param AbstractPluginManager   $pluginManager
     * @param ServiceLocatorInterface $parentLocator
     * @param string                  $serviceName
     * @param BaseException           $previousException
     *
     * @return self
     */
    public static function fromInvalidPluginManagerRequestedServiceName(
        AbstractPluginManager $pluginManager,
        ServiceLocatorInterface $parentLocator,
        $serviceName,
        BaseException $previousException
    ) {
        return new self(
            sprintf(
                "Service \"%s\" has been requested to plugin manager of type \"%s\", but couldn't be retrieved.\n"
                . "A previous exception of type \"%s\" has been raised in the process.\n"
                . "By the way, a service with the name \"%s\" has been found in the parent service locator \"%s\": "
                . 'did you forget to use $parentLocator = $serviceLocator->getServiceLocator() in your factory code?',
                $serviceName,
                get_class($pluginManager),
                get_class($previousException),
                $serviceName,
                get_class($parentLocator)
            ),
            0,
            $previousException
        );
    }
}
