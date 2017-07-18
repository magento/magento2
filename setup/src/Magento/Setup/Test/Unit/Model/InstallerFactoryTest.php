<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\InstallerFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $serviceLocatorMock = $this->getMockForAbstractClass(
            \Zend\ServiceManager\ServiceLocatorInterface::class,
            ['get']
        );
        $serviceLocatorMock->expects($this->any())->method('get')
            ->will($this->returnValueMap($this->getReturnValueMap()));

        $log = $this->getMockForAbstractClass(\Magento\Framework\Setup\LoggerInterface::class);
        $resourceFactoryMock = $this->getMock(\Magento\Setup\Module\ResourceFactory::class, [], [], '', false);
        $resourceFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getMock(
                \Magento\Framework\App\ResourceConnection::class,
                [],
                [],
                '',
                false
            )));
        $installerFactory = new InstallerFactory($serviceLocatorMock, $resourceFactoryMock);
        $installer = $installerFactory->create($log);
        $this->assertInstanceOf(\Magento\Setup\Model\Installer::class, $installer);
    }

    /**
     * @return array
     */
    private function getReturnValueMap()
    {
        return [
            [
                \Magento\Framework\Setup\FilePermissions::class,
                $this->getMock(\Magento\Framework\Setup\FilePermissions::class, [], [], '', false),
            ],
            [
                \Magento\Framework\App\DeploymentConfig\Writer::class,
                $this->getMock(\Magento\Framework\App\DeploymentConfig\Writer::class, [], [], '', false),
            ],
            [
                \Magento\Framework\App\DeploymentConfig\Reader::class,
                $this->getMock(\Magento\Framework\App\DeploymentConfig\Reader::class, [], [], '', false),
            ],
            [
                \Magento\Framework\App\DeploymentConfig::class,
                $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false),
            ],
            [
                \Magento\Framework\Module\ModuleList::class,
                $this->getMock(\Magento\Framework\Module\ModuleList::class, [], [], '', false),
            ],
            [
                \Magento\Framework\Module\ModuleList\Loader::class,
                $this->getMock(\Magento\Framework\Module\ModuleList\Loader::class, [], [], '', false),
            ],
            [
                \Magento\Setup\Model\AdminAccountFactory::class,
                $this->getMock(\Magento\Setup\Model\AdminAccountFactory::class, [], [], '', false),
            ],
            [
                \Magento\Setup\Module\ConnectionFactory::class,
                $this->getMock(\Magento\Setup\Module\ConnectionFactory::class, [], [], '', false),
            ],
            [
                \Magento\Framework\App\MaintenanceMode::class,
                $this->getMock(\Magento\Framework\App\MaintenanceMode::class, [], [], '', false),
            ],
            [
                \Magento\Framework\Filesystem::class,
                $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false),
            ],
            [
                \Magento\Setup\Model\ObjectManagerProvider::class,
                $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false),
            ],
            [
                \Magento\Framework\Model\ResourceModel\Db\TransactionManager::class,
                $this->getMock(\Magento\Framework\Model\ResourceModel\Db\TransactionManager::class, [], [], '', false),
            ],
            [
                \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class,
                $this->getMock(
                    \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class,
                    [],
                    [],
                    '',
                    false
                ),
            ],
            [
                \Magento\Setup\Model\ConfigModel::class,
                $this->getMock(\Magento\Setup\Model\ConfigModel::class, [], [], '', false),
            ],
            [
                \Magento\Framework\App\State\CleanupFiles::class,
                $this->getMock(\Magento\Framework\App\State\CleanupFiles::class, [], [], '', false),
            ],
            [
                \Magento\Setup\Validator\DbValidator::class,
                $this->getMock(\Magento\Setup\Validator\DbValidator::class, [], [], '', false),
            ],
            [
                \Magento\Setup\Module\SetupFactory::class,
                $this->getMock(\Magento\Setup\Module\SetupFactory::class, [], [], '', false),
            ],
            [
                \Magento\Setup\Module\DataSetupFactory::class,
                $this->getMock(\Magento\Setup\Module\DataSetupFactory::class, [], [], '', false),
            ],
            [
                \Magento\Framework\Setup\SampleData\State::class,
                $this->getMock(\Magento\Framework\Setup\SampleData\State::class, [], [], '', false),
            ],
            [
                \Magento\Setup\Model\PhpReadinessCheck::class,
                $this->getMock(\Magento\Setup\Model\PhpReadinessCheck::class, [], [], '', false),
            ],
        ];
    }
}
