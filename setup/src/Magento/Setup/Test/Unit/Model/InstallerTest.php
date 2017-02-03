<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Backend\Setup\ConfigOptionsList;
use Magento\Framework\Config\ConfigOptionsListConstants;
use \Magento\Setup\Model\Installer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Setup\Validator\DbValidator;

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
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * @var \Magento\Setup\Model\AdminAccountFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adminFactory;

    /**
     * @var \Magento\Framework\Setup\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Setup\Model\ConfigModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configModel;

    /**
     * @var CleanupFiles|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanupFiles;

    /**
     * @var DbValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbValidator;

    /**
     * @var \Magento\Setup\Module\SetupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $setupFactory;

    /**
     * @var \Magento\Setup\Module\DataSetupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataSetupFactory;

    /**
     * @var \Magento\Framework\Setup\SampleData\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sampleDataState;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    /**
     * Sample DB configuration segment
     *
     * @var array
     */
    private static $dbConfig = [
        ConfigOptionsListConstants::KEY_HOST => '127.0.0.1',
        ConfigOptionsListConstants::KEY_NAME => 'magento',
        ConfigOptionsListConstants::KEY_USER => 'magento',
        ConfigOptionsListConstants::KEY_PASSWORD => '',
    ];

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject
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
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->adminFactory = $this->getMock('Magento\Setup\Model\AdminAccountFactory', [], [], '', false);
        $this->logger = $this->getMockForAbstractClass('Magento\Framework\Setup\LoggerInterface');
        $this->random = $this->getMock('Magento\Framework\Math\Random', [], [], '', false);
        $this->connection = $this->getMockForAbstractClass('Magento\Framework\DB\Adapter\AdapterInterface');
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->contextMock = $this->getMock('Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $this->configModel = $this->getMock('Magento\Setup\Model\ConfigModel', [], [], '', false);
        $this->cleanupFiles = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $this->dbValidator = $this->getMock('Magento\Setup\Validator\DbValidator', [], [], '', false);
        $this->setupFactory = $this->getMock('Magento\Setup\Module\SetupFactory', [], [], '', false);
        $this->dataSetupFactory = $this->getMock('Magento\Setup\Module\DataSetupFactory', [], [], '', false);
        $this->sampleDataState = $this->getMock('Magento\Framework\Setup\SampleData\State', [], [], '', false);
        $this->componentRegistrar = $this->getMock('Magento\Framework\Component\ComponentRegistrar', [], [], '', false);
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
            $this->moduleList,
            $this->moduleLoader,
            $this->adminFactory,
            $this->logger,
            $connectionFactory,
            $this->maintenanceMode,
            $this->filesystem,
            $objectManagerProvider,
            $this->contextMock,
            $this->configModel,
            $this->cleanupFiles,
            $this->dbValidator,
            $this->setupFactory,
            $this->dataSetupFactory,
            $this->sampleDataState,
            $this->componentRegistrar
        );
    }

    public function testInstall()
    {
        $request = [
            ConfigOptionsListConstants::INPUT_KEY_DB_HOST => '127.0.0.1',
            ConfigOptionsListConstants::INPUT_KEY_DB_NAME => 'magento',
            ConfigOptionsListConstants::INPUT_KEY_DB_USER => 'magento',
            ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY => 'encryption_key',
            ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'backend',
        ];
        $this->config->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap(
                [
                    [ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT, null, true],
                    [ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY, null, true],
                ]
            );
        $allModules = ['Foo_One' => [], 'Bar_Two' => []];
        $this->moduleLoader->expects($this->any())->method('load')->willReturn($allModules);
        $setup = $this->getMock('Magento\Setup\Module\Setup', [], [], '', false);
        $table = $this->getMock('Magento\Framework\DB\Ddl\Table', [], [], '', false);
        $connection = $this->getMockForAbstractClass('Magento\Framework\DB\Adapter\AdapterInterface');
        $setup->expects($this->any())->method('getConnection')->willReturn($connection);
        $table->expects($this->any())->method('addColumn')->willReturn($table);
        $table->expects($this->any())->method('setComment')->willReturn($table);
        $table->expects($this->any())->method('addIndex')->willReturn($table);
        $connection->expects($this->any())->method('newTable')->willReturn($table);
        $resource = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->contextMock->expects($this->any())->method('getResources')->willReturn($resource);
        $resource->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
        $dataSetup = $this->getMock('Magento\Setup\Module\DataSetup', [], [], '', false);
        $cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $cacheManager->expects($this->any())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
        $cacheManager->expects($this->once())->method('setEnabled')->willReturn(['foo', 'bar']);
        $cacheManager->expects($this->any())->method('clean');
        $appState = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Framework\App\State'
        );
        $this->setupFactory->expects($this->atLeastOnce())->method('create')->with($resource)->willReturn($setup);
        $this->dataSetupFactory->expects($this->atLeastOnce())->method('create')->willReturn($dataSetup);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\App\Cache\Manager', [], $cacheManager],
                ['Magento\Framework\App\State', [], $appState],
            ]));
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['Magento\Framework\App\State', $appState],
                ['Magento\Framework\App\Cache\Manager', $cacheManager]
            ]));
        $this->adminFactory->expects($this->once())->method('create')->willReturn(
            $this->getMock('Magento\Setup\Model\AdminAccount', [], [], '', false)
        );
        $this->sampleDataState->expects($this->once())->method('hasError')->willReturn(true);

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
        $this->logger->expects($this->at(21))->method('log')->with('Enabling caches:');
        $this->logger->expects($this->at(25))->method('log')->with('Installing data...');
        $this->logger->expects($this->at(26))->method('log')->with('Data install/update:');
        $this->logger->expects($this->at(27))->method('log')->with("Module 'Foo_One':");
        $this->logger->expects($this->at(29))->method('log')->with("Module 'Bar_Two':");
        $this->logger->expects($this->at(32))->method('log')->with('Installing admin user...');
        $this->logger->expects($this->at(34))->method('log')->with('Caches clearing:');
        $this->logger->expects($this->at(37))->method('log')->with('Disabling Maintenance Mode:');
        $this->logger->expects($this->at(39))->method('log')->with('Post installation file permissions check...');
        $this->logger->expects($this->at(41))->method('log')->with('Write installation date...');
        $this->logger->expects($this->at(43))->method('logSuccess')->with('Magento installation complete.');
        $this->logger->expects($this->at(45))->method('log')
            ->with('Sample Data is installed with errors. See log file for details');
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
     * @expectedExceptionMessage Missing write permissions to the following directories: 'foo' 'bar'
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
        $this->cleanupFiles->expects($this->once())->method('clearCodeGeneratedClasses')->will(
            $this->returnValue(
                [
                    "The directory '/generation' doesn't exist - skipping cleanup",
                ]
            )
        );
        $installer = $this->prepareForUpdateModulesTests();

        $this->logger->expects($this->at(0))->method('log')->with('Cache cleared successfully');
        $this->logger->expects($this->at(1))->method('log')->with('File system cleanup:');
        $this->logger->expects($this->at(2))->method('log')
            ->with('The directory \'/generation\' doesn\'t exist - skipping cleanup');
        $this->logger->expects($this->at(3))->method('log')->with('Updating modules:');
        $installer->updateModulesSequence(false);
    }

    public function testUpdateModulesSequenceKeepGenerated()
    {
        $this->cleanupFiles->expects($this->never())->method('clearCodeGeneratedClasses');

        $installer = $this->prepareForUpdateModulesTests();

        $this->logger->expects($this->at(0))->method('log')->with('Cache cleared successfully');
        $this->logger->expects($this->at(1))->method('log')->with('Updating modules:');
        $installer->updateModulesSequence(true);
    }

    public function testUninstall()
    {
        $this->configReader->expects($this->once())->method('getFiles')->willReturn(['ConfigOne.php', 'ConfigTwo.php']);
        $configDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $configDir
            ->expects($this->exactly(2))
            ->method('getAbsolutePath')
            ->will(
                $this->returnValueMap(
                    [
                        ['ConfigOne.php', '/config/ConfigOne.php'],
                        ['ConfigTwo.php', '/config/ConfigTwo.php']
                    ]
                )
            );
        $this->filesystem
            ->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValueMap([
                [DirectoryList::CONFIG, DriverPool::FILE, $configDir],
            ]));
        $this->logger->expects($this->at(0))->method('log')->with('Starting Magento uninstallation:');
        $this->logger
            ->expects($this->at(2))
            ->method('log')
            ->with('No database connection defined - skipping database cleanup');
        $cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
        $cacheManager->expects($this->once())->method('clean');
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with('Magento\Framework\App\Cache\Manager')
            ->willReturn($cacheManager);
        $this->logger->expects($this->at(1))->method('log')->with('Cache cleared successfully');
        $this->logger->expects($this->at(3))->method('log')->with('File system cleanup:');
        $this->logger
            ->expects($this->at(4))
            ->method('log')
            ->with("The directory '/var' doesn't exist - skipping cleanup");
        $this->logger
            ->expects($this->at(5))
            ->method('log')
            ->with("The directory '/static' doesn't exist - skipping cleanup");
        $this->logger
            ->expects($this->at(6))
            ->method('log')
            ->with("The file '/config/ConfigOne.php' doesn't exist - skipping cleanup");
        $this->logger
            ->expects($this->at(7))
            ->method('log')
            ->with("The file '/config/ConfigTwo.php' doesn't exist - skipping cleanup");
        $this->logger->expects($this->once())->method('logSuccess')->with('Magento uninstallation complete.');
        $this->cleanupFiles->expects($this->once())->method('clearAllFiles')->will(
            $this->returnValue(
                [
                    "The directory '/var' doesn't exist - skipping cleanup",
                    "The directory '/static' doesn't exist - skipping cleanup"
                ]
            )
        );

        $this->object->uninstall();
    }

    public function testCleanupDb()
    {
        $this->config->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT)
            ->willReturn(self::$dbConfig);
        $this->connection->expects($this->at(0))->method('quoteIdentifier')->with('magento')->willReturn('`magento`');
        $this->connection->expects($this->at(1))->method('query')->with('DROP DATABASE IF EXISTS `magento`');
        $this->connection->expects($this->at(2))->method('query')->with('CREATE DATABASE IF NOT EXISTS `magento`');
        $this->logger->expects($this->once())->method('log')->with('Cleaning up database `magento`');
        $this->object->cleanupDb();
    }

    /**
     * Prepare mocks for update modules tests and returns the installer to use
     *
     * @return Installer
     */
    private function prepareForUpdateModulesTests()
    {
        $allModules = [
            'Foo_One' => [],
            'Bar_Two' => [],
            'New_Module' => [],
        ];

        $cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
        $cacheManager->expects($this->once())->method('clean');
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['Magento\Framework\App\Cache\Manager', $cacheManager]
            ]));
        $this->moduleLoader->expects($this->once())->method('load')->willReturn($allModules);

        $expectedModules = [
            ConfigFilePool::APP_CONFIG => [
                'modules' => [
                    'Bar_Two' => 0,
                    'Foo_One' => 1,
                    'New_Module' => 1
                ]
            ]
        ];

        $this->config->expects($this->atLeastOnce())
            ->method('get')
            ->with(ConfigOptionsListConstants::KEY_MODULES)
            ->willReturn(true);

        $newObject = $this->createObject(false, false);
        $this->configReader->expects($this->once())->method('load')
            ->willReturn(['modules' => ['Bar_Two' => 0, 'Foo_One' => 1, 'Old_Module' => 0] ]);
        $this->configWriter->expects($this->once())->method('saveConfig')->with($expectedModules);

        return $newObject;
    }
}

namespace Magento\Setup\Model;

/**
 * Mocking autoload function
 *
 * @returns array
 */
function spl_autoload_functions()
{
    return ['mock_function_one', 'mock_function_two'];
}
