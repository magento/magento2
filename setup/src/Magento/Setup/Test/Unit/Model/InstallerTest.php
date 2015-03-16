<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\Installer;
use \Magento\Setup\Model\DeploymentConfigMapper;

use Magento\Framework\App\DeploymentConfig\DbConfig;
use Magento\Framework\App\DeploymentConfig\EncryptConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\Installer
     */
    private $object;

    /**
     * @var \Magento\Setup\Model\FilePermissions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filePermissions;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Writer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReader;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleLoader;

    /**
     * @var \Magento\Framework\Module\ModuleList\DeploymentConfigFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigFactory;

    /**
     * @var \Magento\Framework\Module\ModuleList\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * @var \Magento\Setup\Model\AdminAccountFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adminFactory;

    /**
     * @var \Magento\Setup\Model\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    private $random;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Setup\Model\SampleData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sampleData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * Sample DB configuration segment
     *
     * @var array
     */
    private static $dbConfig = [
        DbConfig::KEY_PREFIX => '',
        'connection' => [
            'default' => [
                DbConfig::KEY_HOST => '127.0.0.1',
                DbConfig::KEY_NAME => 'magento',
                DbConfig::KEY_USER => 'magento',
                DbConfig::KEY_PASS => '',
            ],
        ],
    ];

    /**
     * @var Magento\Framework\Model\Resource\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    protected function setUp()
    {
        $this->filePermissions = $this->getMock('Magento\Setup\Model\FilePermissions', [], [], '', false);
        $this->configWriter = $this->getMock('Magento\Framework\App\DeploymentConfig\Writer', [], [], '', false);
        $this->configReader = $this->getMock('Magento\Framework\App\DeploymentConfig\Reader', [], [], '', false);
        $this->config = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);

        $this->moduleList = $this->getMockForAbstractClass('Magento\Framework\Module\ModuleListInterface');
        $this->moduleList->expects($this->any())->method('getOne')->willReturn(
            ['setup_version' => '2.0.0']
        );
        $this->moduleList->expects($this->any())->method('getNames')->willReturn(
            ['Foo_One', 'Bar_Two']
        );
        $this->moduleLoader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $this->deploymentConfigFactory = $this->getMock(
            'Magento\Framework\Module\ModuleList\DeploymentConfigFactory',
            [],
            [],
            '',
            false
        );
        $this->deploymentConfig = $this->getMock(
            'Magento\Framework\Module\ModuleList\DeploymentConfig',
            [],
            [],
            '',
            false
        );
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->adminFactory = $this->getMock('Magento\Setup\Model\AdminAccountFactory', [], [], '', false);
        $this->logger = $this->getMockForAbstractClass('Magento\Setup\Model\LoggerInterface');
        $this->random = $this->getMock('Magento\Framework\Math\Random', [], [], '', false);
        $this->connection = $this->getMockForAbstractClass('Magento\Framework\DB\Adapter\AdapterInterface');
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->sampleData = $this->getMock('Magento\Setup\Model\SampleData', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->contextMock = $this->getMock('Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $this->object = $this->createObject();
    }

    /**
     * Instantiates the object with mocks
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|bool $connectionFactory
     * @param \PHPUnit_Framework_MockObject_MockObject|bool $objectManagerProvider
     * @return Installer
     */
    private function createObject($connectionFactory = false, $objectManagerProvider = false)
    {
        if (!$connectionFactory) {
            $connectionFactory = $this->getMock('Magento\Setup\Module\ConnectionFactory', [], [], '', false);
            $connectionFactory->expects($this->any())->method('create')->willReturn($this->connection);
        }
        if (!$objectManagerProvider) {
            $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
            $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        }

        return new Installer(
            $this->filePermissions,
            $this->configWriter,
            $this->configReader,
            $this->config,
            $this->deploymentConfigFactory,
            $this->moduleList,
            $this->moduleLoader,
            $this->directoryList,
            $this->adminFactory,
            $this->logger,
            $this->random,
            $connectionFactory,
            $this->maintenanceMode,
            $this->filesystem,
            $this->sampleData,
            $objectManagerProvider,
            $this->contextMock
        );
    }

    public function testInstall()
    {
        $request = [
            DeploymentConfigMapper::KEY_DB_HOST => '127.0.0.1',
            DeploymentConfigMapper::KEY_DB_NAME => 'magento',
            DeploymentConfigMapper::KEY_DB_USER => 'magento',
            DeploymentConfigMapper::KEY_ENCRYPTION_KEY => 'encryption_key',
            DeploymentConfigMapper::KEY_BACKEND_FRONTNAME => 'backend',
        ];
        $this->config->expects($this->atLeastOnce())->method('isAvailable')->willReturn(true);
        $this->config->expects($this->any())->method('getSegment')->will($this->returnValueMap([
            [DbConfig::CONFIG_KEY, self::$dbConfig],
            [EncryptConfig::CONFIG_KEY, [EncryptConfig::KEY_ENCRYPTION_KEY => 'encryption_key']]
        ]));
        $allModules = ['Foo_One' => [], 'Bar_Two' => []];
        $this->moduleLoader->expects($this->any())->method('load')->willReturn($allModules);
        $modules = ['Foo_One' => 1, 'Bar_Two' => 1 ];
        $this->deploymentConfig->expects($this->any())->method('getData')->willReturn($modules);
        $this->deploymentConfigFactory->expects($this->any())->method('create')->with($modules)
            ->willReturn($this->deploymentConfig);
        $setup = $this->getMock('Magento\Setup\Module\Setup', [], [], '', false);
        $table = $this->getMock('Magento\Framework\DB\Ddl\Table', [], [], '', false);
        $connection = $this->getMockForAbstractClass('Magento\Framework\DB\Adapter\AdapterInterface');
        $setup->expects($this->any())->method('getConnection')->willReturn($connection);
        $table->expects($this->any())->method('addColumn')->willReturn($table);
        $table->expects($this->any())->method('setComment')->willReturn($table);
        $table->expects($this->any())->method('addIndex')->willReturn($table);
        $connection->expects($this->any())->method('newTable')->willReturn($table);
        $resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->contextMock->expects($this->any())->method('getResources')->willReturn($resource);
        $resource->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
        $dataSetup = $this->getMock('Magento\Setup\Module\DataSetup', [], [], '', false);
        $cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
        $cacheManager->expects($this->once())->method('setEnabled')->willReturn(['foo', 'bar']);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Setup\Module\Setup', ['resource' => $resource], $setup],
                ['Magento\Setup\Module\DataSetup', [], $dataSetup],
                ['Magento\Framework\App\Cache\Manager', [], $cacheManager],
            ]));
        $this->adminFactory->expects($this->once())->method('create')->willReturn(
            $this->getMock('Magento\Setup\Model\AdminAccount', [], [], '', false)
        );

        $this->logger->expects($this->at(0))->method('log')->with('Starting Magento installation:');
        $this->logger->expects($this->at(1))->method('log')->with('File permissions check...');
        // at(2) invokes logMeta()
        $this->logger->expects($this->at(3))->method('log')->with('Enabling Maintenance Mode...');
        // at(4) - logMeta and so on...
        $this->logger->expects($this->at(5))->method('log')->with('Installing deployment configuration...');
        $this->logger->expects($this->at(7))->method('log')->with('Installing database schema:');
        $this->logger->expects($this->at(9))->method('log')->with("Module 'Foo_One':");
        $this->logger->expects($this->at(11))->method('log')->with("Module 'Bar_Two':");
        $this->logger->expects($this->at(13))->method('log')->with('Schema post-updates:');
        $this->logger->expects($this->at(14))->method('log')->with("Module 'Foo_One':");
        $this->logger->expects($this->at(16))->method('log')->with("Module 'Bar_Two':");
        $this->logger->expects($this->at(19))->method('log')->with('Installing user configuration...');
        $this->logger->expects($this->at(21))->method('log')->with('Installing data...');
        $this->logger->expects($this->at(22))->method('log')->with('Data install/update:');
        $this->logger->expects($this->at(23))->method('log')->with("Module 'Foo_One':");
        $this->logger->expects($this->at(25))->method('log')->with("Module 'Bar_Two':");
        $this->logger->expects($this->at(28))->method('log')->with('Installing admin user...');
        $this->logger->expects($this->at(30))->method('log')->with('Enabling caches:');
        $this->logger->expects($this->at(31))->method('log')->with('Current status:');
        $this->logger->expects($this->at(34))->method('log')->with('Disabling Maintenance Mode:');
        $this->logger->expects($this->at(36))->method('log')->with('Post installation file permissions check...');
        $this->logger->expects($this->once())->method('logSuccess')->with('Magento installation complete.');
        $this->object->install($request);
    }

    public function testCheckInstallationFilePermissions()
    {
        $this->filePermissions
            ->expects($this->once())
            ->method('getMissingWritableDirectoriesForInstallation')
            ->willReturn([]);
        $this->object->checkInstallationFilePermissions();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Missing writing permissions to the following directories: 'foo' 'bar'
     */
    public function testCheckInstallationFilePermissionsError()
    {
        $this->filePermissions
            ->expects($this->once())
            ->method('getMissingWritableDirectoriesForInstallation')
            ->willReturn(['foo', 'bar']);
        $this->object->checkInstallationFilePermissions();
    }

    public function testCheckApplicationFilePermissions()
    {
        $this->filePermissions
            ->expects($this->once())
            ->method('getUnnecessaryWritableDirectoriesForApplication')
            ->willReturn(['foo', 'bar']);
        $expectedMessage = "For security, remove write permissions from these directories: 'foo' 'bar'";
        $this->logger->expects($this->once())->method('log')->with($expectedMessage);
        $this->object->checkApplicationFilePermissions();
        $this->assertSame(['message' => [$expectedMessage]], $this->object->getInstallInfo());
    }

    public function testUpdateModulesSequence()
    {
        $varDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $varDir->expects($this->exactly(2))->method('getAbsolutePath')->willReturn('/var');
        $this->filesystem
            ->expects($this->exactly(2))
            ->method('getDirectoryWrite')
            ->willReturn($varDir);

        $allModules = [
            'Foo_One' => [],
            'Bar_Two' => [],
            'New_Module' => [],
        ];
        $this->moduleLoader->expects($this->once())->method('load')->willReturn($allModules);

        $expectedModules = [
            'Bar_Two' => 0,
            'Foo_One' => 1,
            'New_Module' => 1
        ];

        $this->config->expects($this->atLeastOnce())->method('isAvailable')->willReturn(true);
        $this->deploymentConfigFactory->expects($this->once())->method('create')->with($expectedModules)
            ->willReturn($this->deploymentConfig);

        $newObject = $this->createObject(false, false);
        $this->configReader->expects($this->once())->method('load')
            ->willReturn(['modules' => ['Bar_Two' => 0, 'Foo_One' => 1, 'Old_Module' => 0] ]);
        $this->configWriter->expects($this->once())->method('update')->with($this->deploymentConfig);
        $this->logger->expects($this->at(0))->method('log')->with('File system cleanup:');
        $this->logger->expects($this->at(1))->method('log')
            ->with('The directory \'/var\' doesn\'t exist - skipping cleanup');
        $this->logger->expects($this->at(3))->method('log')->with('Updating modules:');
        $newObject->updateModulesSequence();
    }

    public function testUninstall()
    {
        $this->config->expects($this->once())->method('isAvailable')->willReturn(false);
        $varDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $varDir->expects($this->once())->method('getAbsolutePath')->willReturn('/var');
        $staticDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $staticDir->expects($this->once())->method('getAbsolutePath')->willReturn('/static');
        $configDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $configDir->expects($this->once())->method('getAbsolutePath')->willReturn('/config/config.php');
        $this->filesystem
            ->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValueMap([
                [DirectoryList::VAR_DIR, DriverPool::FILE, $varDir],
                [DirectoryList::STATIC_VIEW, DriverPool::FILE, $staticDir],
                [DirectoryList::CONFIG, DriverPool::FILE, $configDir],
            ]));
        $this->logger->expects($this->at(0))->method('log')->with('Starting Magento uninstallation:');
        $this->logger
            ->expects($this->at(1))
            ->method('log')
            ->with('No database connection defined - skipping database cleanup');
        $this->logger->expects($this->at(2))->method('log')->with('File system cleanup:');
        $this->logger
            ->expects($this->at(3))
            ->method('log')
            ->with("The directory '/var' doesn't exist - skipping cleanup");
        $this->logger
            ->expects($this->at(4))
            ->method('log')
            ->with("The directory '/static' doesn't exist - skipping cleanup");
        $this->logger
            ->expects($this->at(5))
            ->method('log')
            ->with("The file '/config/config.php' doesn't exist - skipping cleanup");
        $this->logger->expects($this->once())->method('logSuccess')->with('Magento uninstallation complete.');

        $this->object->uninstall();
    }

    public function testCleanupDb()
    {
        $this->config->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->config->expects($this->once())
            ->method('getSegment')
            ->with(DbConfig::CONFIG_KEY)
            ->willReturn(self::$dbConfig);
        $this->connection->expects($this->at(0))->method('quoteIdentifier')->with('magento')->willReturn('`magento`');
        $this->connection->expects($this->at(1))->method('query')->with('DROP DATABASE IF EXISTS `magento`');
        $this->connection->expects($this->at(2))->method('query')->with('CREATE DATABASE IF NOT EXISTS `magento`');
        $this->logger->expects($this->once())->method('log')->with('Recreating database `magento`');
        $this->object->cleanupDb();
    }

    public function testCheckDatabaseConnection()
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT version()')
            ->willReturn('5.6.0-0ubuntu0.12.04.1');
        $this->assertEquals(true, $this->object->checkDatabaseConnection('name', 'host', 'user', 'password'));
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessage Database connection failure.
     */
    public function testCheckDatabaseConnectionFailed()
    {
        $connectionFactory = $this->getMock('Magento\Setup\Module\ConnectionFactory', [], [], '', false);
        $connectionFactory->expects($this->once())->method('create')->willReturn(false);
        $object = $this->createObject($connectionFactory);
        $object->checkDatabaseConnection('name', 'host', 'user', 'password');
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessage Sorry, but we support MySQL version
     */
    public function testCheckDatabaseConnectionIncompatible()
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT version()')
            ->willReturn('5.5.40-0ubuntu0.12.04.1');
        $this->object->checkDatabaseConnection('name', 'host', 'user', 'password');
    }
}
