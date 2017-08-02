<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Setup\Module\ResourceFactory;
use Magento\Framework\App\ErrorHandler;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Setup\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class InstallerFactory
{
    /**
     * Zend Framework's service locator
     *
     * @var ServiceLocatorInterface
     * @since 2.0.0
     */
    protected $serviceLocator;

    /**
     * @var ResourceFactory
     * @since 2.0.0
     */
    private $resourceFactory;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param ResourceFactory $resourceFactory
     * @since 2.0.0
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, ResourceFactory $resourceFactory)
    {
        $this->serviceLocator = $serviceLocator;
        $this->resourceFactory = $resourceFactory;
        // For Setup Wizard we are using our customized error handler
        $handler = new ErrorHandler();
        set_error_handler([$handler, 'handler']);
    }

    /**
     * Factory method for installer object
     *
     * @param LoggerInterface $log
     * @return Installer
     * @since 2.0.0
     */
    public function create(LoggerInterface $log)
    {
        return new Installer(
            $this->serviceLocator->get(\Magento\Framework\Setup\FilePermissions::class),
            $this->serviceLocator->get(\Magento\Framework\App\DeploymentConfig\Writer::class),
            $this->serviceLocator->get(\Magento\Framework\App\DeploymentConfig\Reader::class),
            $this->serviceLocator->get(\Magento\Framework\App\DeploymentConfig::class),
            $this->serviceLocator->get(\Magento\Framework\Module\ModuleList::class),
            $this->serviceLocator->get(\Magento\Framework\Module\ModuleList\Loader::class),
            $this->serviceLocator->get(\Magento\Setup\Model\AdminAccountFactory::class),
            $log,
            $this->serviceLocator->get(\Magento\Setup\Module\ConnectionFactory::class),
            $this->serviceLocator->get(\Magento\Framework\App\MaintenanceMode::class),
            $this->serviceLocator->get(\Magento\Framework\Filesystem::class),
            $this->serviceLocator->get(\Magento\Setup\Model\ObjectManagerProvider::class),
            new \Magento\Framework\Model\ResourceModel\Db\Context(
                $this->getResource(),
                $this->serviceLocator->get(\Magento\Framework\Model\ResourceModel\Db\TransactionManager::class),
                $this->serviceLocator->get(\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class)
            ),
            $this->serviceLocator->get(\Magento\Setup\Model\ConfigModel::class),
            $this->serviceLocator->get(\Magento\Framework\App\State\CleanupFiles::class),
            $this->serviceLocator->get(\Magento\Setup\Validator\DbValidator::class),
            $this->serviceLocator->get(\Magento\Setup\Module\SetupFactory::class),
            $this->serviceLocator->get(\Magento\Setup\Module\DataSetupFactory::class),
            $this->serviceLocator->get(\Magento\Framework\Setup\SampleData\State::class),
            new \Magento\Framework\Component\ComponentRegistrar(),
            $this->serviceLocator->get(\Magento\Setup\Model\PhpReadinessCheck::class)
        );
    }

    /**
     * creates Resource Factory
     *
     * @return Resource
     * @since 2.0.0
     */
    private function getResource()
    {
        $deploymentConfig = $this->serviceLocator->get(\Magento\Framework\App\DeploymentConfig::class);
        return $this->resourceFactory->create($deploymentConfig);
    }
}
