<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManager;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\Setup\FilePermissions;
use Magento\Framework\Setup\LoggerInterface;
use Magento\Framework\Setup\SampleData\State;
use Magento\Framework\Setup\SchemaPersistor;
use Magento\Setup\Model\AdminAccountFactory;
use Magento\Setup\Model\ConfigModel;
use Magento\Setup\Model\DeclarationInstaller;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\PhpReadinessCheck;
use Magento\Setup\Module\ConnectionFactory;
use Magento\Setup\Module\DataSetupFactory;
use Magento\Setup\Module\ResourceFactory;
use Magento\Setup\Module\SetupFactory;
use Magento\Setup\Validator\DbValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallerFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerProvider|MockObject
     */
    private $objectManagerProviderMock;

    public function testCreate()
    {
        $this->objectManagerProviderMock = $this->getMockBuilder(ObjectManagerProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [DeclarationInstaller::class, $this->createMock(DeclarationInstaller::class)],
                    [SchemaPersistor::class, $this->createMock(SchemaPersistor::class)],
                ]
            );
        $this->objectManagerProviderMock->expects($this->any())
            ->method('get')
            ->willReturn($objectManagerMock);
        /** @var ServiceLocatorInterface|MockObject $serviceLocatorMock */
        $serviceLocatorMock = $this->getMockBuilder(
            ServiceLocatorInterface::class
        )->onlyMethods(
            ['get']
        )->getMockForAbstractClass();
        $serviceLocatorMock->expects($this->any())->method('get')
            ->willReturnMap($this->getReturnValueMap());

        /** @var LoggerInterface|MockObject $log */
        $log = $this->getMockForAbstractClass(LoggerInterface::class);
        /** @var ResourceFactory|MockObject $resourceFactoryMock */
        $resourceFactoryMock = $this->createMock(ResourceFactory::class);
        $resourceFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->createMock(ResourceConnection::class));
        $installerFactory = new InstallerFactory($serviceLocatorMock, $resourceFactoryMock);
        $installer = $installerFactory->create($log);
        $this->assertInstanceOf(Installer::class, $installer);
    }

    /**
     * @return array
     */
    private function getReturnValueMap()
    {
        return [
            [
                FilePermissions::class,
                $this->createMock(FilePermissions::class),
            ],
            [
                Writer::class,
                $this->createMock(Writer::class),
            ],
            [
                Reader::class,
                $this->createMock(Reader::class),
            ],
            [
                DeploymentConfig::class,
                $this->createMock(DeploymentConfig::class),
            ],
            [
                ModuleList::class,
                $this->createMock(ModuleList::class),
            ],
            [
                Loader::class,
                $this->createMock(Loader::class),
            ],
            [
                AdminAccountFactory::class,
                $this->createMock(AdminAccountFactory::class),
            ],
            [
                ConnectionFactory::class,
                $this->createMock(ConnectionFactory::class),
            ],
            [
                MaintenanceMode::class,
                $this->createMock(MaintenanceMode::class),
            ],
            [
                Filesystem::class,
                $this->createMock(Filesystem::class),
            ],
            [
                ObjectManagerProvider::class,
                $this->objectManagerProviderMock
            ],
            [
                TransactionManager::class,
                $this->createMock(TransactionManager::class),
            ],
            [
                ObjectRelationProcessor::class,
                $this->createMock(ObjectRelationProcessor::class),
            ],
            [
                ConfigModel::class,
                $this->createMock(ConfigModel::class),
            ],
            [
                CleanupFiles::class,
                $this->createMock(CleanupFiles::class),
            ],
            [
                DbValidator::class,
                $this->createMock(DbValidator::class),
            ],
            [
                SetupFactory::class,
                $this->createMock(SetupFactory::class),
            ],
            [
                DataSetupFactory::class,
                $this->createMock(DataSetupFactory::class),
            ],
            [
                State::class,
                $this->createMock(State::class),
            ],
            [
                PhpReadinessCheck::class,
                $this->createMock(PhpReadinessCheck::class),
            ],
        ];
    }
}
