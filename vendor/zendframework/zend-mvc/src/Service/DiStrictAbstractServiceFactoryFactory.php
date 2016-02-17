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

class DiStrictAbstractServiceFactoryFactory implements FactoryInterface
{
    /**
     * Class responsible for instantiating a DiStrictAbstractServiceFactory
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return DiStrictAbstractServiceFactory
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $diAbstractFactory = new DiStrictAbstractServiceFactory(
            $serviceLocator->get('Di'),
            DiStrictAbstractServiceFactory::USE_SL_BEFORE_DI
        );
        $config = $serviceLocator->get('Config');

        if (isset($config['di']['allowed_controllers'])) {
            $diAbstractFactory->setAllowedServiceNames($config['di']['allowed_controllers']);
        }

        return $diAbstractFactory;
    }
}
