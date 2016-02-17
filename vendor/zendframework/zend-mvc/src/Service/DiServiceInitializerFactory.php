<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\ServiceManager\Di\DiServiceInitializer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DiServiceInitializerFactory implements FactoryInterface
{
    /**
     * Class responsible for instantiating a DiServiceInitializer
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return DiServiceInitializer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DiServiceInitializer($serviceLocator->get('Di'), $serviceLocator);
    }
}
