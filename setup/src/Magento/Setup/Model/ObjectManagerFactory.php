<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\Framework\App\Bootstrap;

/**
 * Factory for creating object manager
 *
 * The Setup application needs this wrapper due to complexity of creating factory in Magento application
 * and in order to link Zend Framework's service locator and Magento object manager
 */
class ObjectManagerFactory
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function create()
    {
        $initParams = $this->serviceLocator->get(InitParamListener::BOOTSTRAP_PARAM);
        $factory = Bootstrap::createObjectManagerFactory(BP, $initParams);
        return $factory->create($initParams);
    }
}
