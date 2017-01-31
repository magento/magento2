<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\ObjectManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\DeploymentConfig;

/**
 * Object manager provider
 *
 * Links Zend Framework's service locator and Magento object manager.
 * Guaranties single object manager per application run.
 * Hides complexity of creating Magento object manager
 */
class ObjectManagerProvider
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Retrieve object manager.
     *
     * @return ObjectManagerInterface
     * @throws \Magento\Setup\Exception
     */
    public function get()
    {
        if (null === $this->objectManager) {
            $initParams = $this->serviceLocator->get(InitParamListener::BOOTSTRAP_PARAM);
            $factory = Bootstrap::createObjectManagerFactory(BP, $initParams);
            $this->objectManager = $factory->create($initParams);
        }
        return $this->objectManager;
    }

    /**
     * Causes object manager to be reinitialized the next time it is retrieved.
     *
     * @return void
     */
    public function reset()
    {
        $this->objectManager = null;
    }

    /**
     * Sets object manager
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function setObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns ObjectManagerFactory
     *
     * @param array $initParams
     * @return \Magento\Framework\App\ObjectManagerFactory
     */
    public function getObjectManagerFactory($initParams = [])
    {
        return Bootstrap::createObjectManagerFactory(
            BP,
            $initParams
        );
    }
}
