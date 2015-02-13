<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, DeploymentConfig $deploymentConfig)
    {
        $this->serviceLocator = $serviceLocator;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     * @throws \Magento\Setup\Exception
     */
    public function get()
    {
        if (null === $this->objectManager) {
//            if (!$this->deploymentConfig->isAvailable()) {
//                throw new \Magento\Setup\Exception(
//                    "Can't instantiate object manager due to absence of deployment configuration. "
//                    . "Please, install the deployment configuration first"
//                );
//            }
            $initParams = $this->serviceLocator->get(InitParamListener::BOOTSTRAP_PARAM);
            $factory = Bootstrap::createObjectManagerFactory(BP, $initParams);
            $this->objectManager = $factory->create($initParams);
        }
        return $this->objectManager;
    }
}
