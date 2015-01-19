<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module;

use Magento\Setup\Model\LoggerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SetupFactory
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param ResourceFactory $resourceFactory
     */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        \Magento\Setup\Module\ResourceFactory $resourceFactory
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * Creates Setup
     *
     * @return Setup
     */
    public function createSetup()
    {
        return new Setup($this->getResource());
    }

    /**
     * Creates SetupModule
     *
     * @param LoggerInterface $log
     * @param string $moduleName
     * @return SetupModule
     */
    public function createSetupModule(LoggerInterface $log, $moduleName)
    {
        return new SetupModule(
            $log,
            $this->serviceLocator->get('Magento\Framework\Module\ModuleList'),
            $this->serviceLocator->get('Magento\Setup\Module\Setup\FileResolver'),
            $moduleName,
            $this->getResource()
        );
    }

    private function getResource()
    {
        $deploymentConfig = new \Magento\Framework\App\DeploymentConfig(
            $this->serviceLocator->get('Magento\Framework\App\DeploymentConfig\Reader'),
            []
        );
        return $this->resourceFactory->create($deploymentConfig);
    }
}
