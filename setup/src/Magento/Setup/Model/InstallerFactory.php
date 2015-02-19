<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Setup\Module\ResourceFactory;

class InstallerFactory
{
    /**
     * Zend Framework's service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        ResourceFactory $resourceFactory
    )
    {
        $this->serviceLocator = $serviceLocator;
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * Factory method for installer object
     *
     * @param LoggerInterface $log
     * @return Installer
     */
    public function create(LoggerInterface $log)
    {
        return new Installer(
            $this->serviceLocator->get('Magento\Setup\Model\FilePermissions'),
            $this->serviceLocator->get('Magento\Framework\App\DeploymentConfig\Writer'),
            $this->serviceLocator->get('Magento\Framework\App\DeploymentConfig'),
            $this->serviceLocator->get('Magento\Framework\Module\ModuleList'),
            $this->serviceLocator->get('Magento\Framework\Module\ModuleList\Loader'),
            $this->serviceLocator->get('Magento\Framework\App\Filesystem\DirectoryList'),
            $this->serviceLocator->get('Magento\Setup\Model\AdminAccountFactory'),
            $log,
            $this->serviceLocator->get('Magento\Framework\Math\Random'),
            $this->serviceLocator->get('Magento\Setup\Module\ConnectionFactory'),
            $this->serviceLocator->get('Magento\Framework\App\MaintenanceMode'),
            $this->serviceLocator->get('Magento\Framework\Filesystem'),
            $this->serviceLocator->get('Magento\Setup\Model\SampleData'),
            $this->serviceLocator->get('Magento\Setup\Model\ObjectManagerFactory'),
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
