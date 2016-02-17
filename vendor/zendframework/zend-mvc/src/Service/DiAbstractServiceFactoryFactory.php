<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\ServiceManager\Di\DiAbstractServiceFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class DiAbstractServiceFactoryFactory implements FactoryInterface
{
    /**
     * Class responsible for instantiating a DiAbstractServiceFactory
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return DiAbstractServiceFactory
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $factory = new DiAbstractServiceFactory($serviceLocator->get('Di'), DiAbstractServiceFactory::USE_SL_BEFORE_DI);

        if ($serviceLocator instanceof ServiceManager) {
            /* @var $serviceLocator ServiceManager */
            $serviceLocator->addAbstractFactory($factory, false);
        }

        return $factory;
    }
}
