<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model {

    use Magento\Backend\Setup\ConfigOptionsList;
    use Magento\Framework\App\Area;
    use Magento\Framework\App\Cache\Manager;
    use Magento\Framework\App\DeploymentConfig;
    use Magento\Framework\App\DeploymentConfig\Reader;
    use Magento\Framework\App\DeploymentConfig\Writer;
    use Magento\Framework\App\Filesystem\DirectoryList;
    use Magento\Framework\App\MaintenanceMode;
    use Magento\Framework\App\ResourceConnection;
    use Magento\Framework\App\State\CleanupFiles;
    use Magento\Framework\Component\ComponentRegistrar;
    use Magento\Framework\Config\ConfigOptionsListConstants;
    use Magento\Framework\Config\File\ConfigFilePool;
    use Magento\Framework\DB\Adapter\AdapterInterface;
    use Magento\Framework\DB\Ddl\Table;
    use Magento\Framework\Filesystem;
    use Magento\Framework\Filesystem\Directory\WriteInterface;
    use Magento\Framework\Filesystem\DriverPool;
    use Magento\Framework\Math\Random;
    use Magento\Framework\Model\ResourceModel\Db\Context;
    use Magento\Framework\Module\ModuleList\Loader;
    use Magento\Framework\Module\ModuleListInterface;
    use Magento\Framework\ObjectManagerInterface;
    use Magento\Framework\Registry;
    use Magento\Framework\Setup\FilePermissions;
    use Magento\Framework\Setup\LoggerInterface;
    use Magento\Framework\Setup\Patch\PatchApplier;
    use Magento\Framework\Setup\Patch\PatchApplierFactory;
    use Magento\Framework\Setup\SampleData\State;
    use Magento\Framework\Setup\SchemaListener;
    use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
    use Magento\Setup\Controller\ResponseTypeInterface;
    use Magento\Setup\Model\AdminAccount;
    use Magento\Setup\Model\AdminAccountFactory;
    use Magento\Setup\Model\ConfigModel;
    use Magento\Setup\Model\DeclarationInstaller;
    use Magento\Setup\Model\Installer;
    use Magento\Setup\Model\ObjectManagerProvider;
    use Magento\Setup\Model\PhpReadinessCheck;
    use Magento\Setup\Model\SearchConfig;
    use Magento\Setup\Module\ConnectionFactory;
    use Magento\Setup\Module\DataSetup;
    use Magento\Setup\Module\DataSetupFactory;
    use Magento\Setup\Module\Setup;
    use Magento\Setup\Module\SetupFactory;
    use Magento\Setup\Validator\DbValidator;
    use PHPUnit\Framework\MockObject\MockObject;
    use PHPUnit\Framework\TestCase;

    /**
     * @SuppressWarnings(PHPMD.TooManyFields)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    class InstallerTest extends TestCase
    {
        /**
         * @var Installer
         */
        private $object;

        /**
         * @var FilePermissions|MockObject
         */
        private $filePermissionsMock;

        /**
         * @var Writer|MockObject
         */
        private $configWriterMock;

        /**
         * @var Reader|MockObject
         */
        private $configReaderMock;

        /**
         * @var DeploymentConfig|MockObject
         */
        private $configMock;

        /**
         * @var ModuleListInterface|MockObject
         */
        private $moduleListMock;

        /**
         * @var Loader|MockObject
         */
        private $moduleLoaderMock;

        /**
         * @var DirectoryList|MockObject
         */
        private $directoryListMock;

        /**
         * @var AdminAccountFactory|MockObject
         */
        private $adminFactoryMock;

        /**
         * @var LoggerInterface|MockObject
         */
        private $loggerMock;

        /**
         * @var Random|MockObject
         */
        private $randomMock;

        /**
         * @var AdapterInterface|MockObject
         */
        private $connectionMock;

        /**
         * @var MaintenanceMode|MockObject
         */
        private $maintenanceModeMock;

        /**
         * @var Filesystem|MockObject
         */
        private $filesystemMock;

        /**
         * @var MockObject
         */
        private $objectManager;

        /**
         * @var ConfigModel|MockObject
         */
        private $configModelMock;

        /**
         * @var CleanupFiles|MockObject
         */
        private $cleanupFilesMock;

        /**
         * @var DbValidator|MockObject
         */
        private $dbValidatorMock;

        /**
         * @var SetupFactory|MockObject
         */
        private $setupFactoryMock;

        /**
         * @var DataSetupFactory|MockObject
         */
        private $dataSetupFactoryMock;

        /**
         * @var State|MockObject
         */
        private $sampleDataStateMock;

        /**
         * @var ComponentRegistrar|MockObject
         */
        private $componentRegistrarMock;

        /**
         * @var PhpReadinessCheck|MockObject
         */
        private $phpReadinessCheckMock;

        /**
         * @var DeclarationInstaller|MockObject
         */
        private $declarationInstallerMock;

        /**
         * @var SchemaListener|MockObject
         */
        private $schemaListenerMock;

        /**
         * @var Context|MockObject
         */
        private $contextMock;

        /**
         * @var PatchApplier|MockObject
         */
        private $patchApplierMock;

        /**
         * @var PatchApplierFactory|MockObject
         */
        private $patchApplierFactoryMock;

        /**
         * Sample DB configuration segment
         * @var array
         */
        private static $dbConfig = [
            'default' => [
                ConfigOptionsListConstants::KEY_HOST => '127.0.0.1',
                ConfigOptionsListConstants::KEY_NAME => 'magento',
                ConfigOptionsListConstants::KEY_USER => 'magento',
                ConfigOptionsListConstants::KEY_PASSWORD => '',
            ]
        ];

        protected function setUp(): void
        {
            $this->filePermissionsMock = $this->createMock(FilePermissions::class);
            $this->configWriterMock = $this->createMock(Writer::class);
            $this->configReaderMock = $this->createMock(Reader::class);
            $this->configMock = $this->createMock(DeploymentConfig::class);

            $this->moduleListMock = $this->getMockForAbstractClass(ModuleListInterface::class);
            $this->moduleListMock->expects($this->any())
                ->method('getOne')
                ->willReturn(
                    ['setup_version' => '2.0.0']
                );
            $this->moduleListMock->expects($this->any())
                ->method('getNames')
                ->willReturn(
                    ['Foo_One', 'Bar_Two']
                );
            $this->moduleLoaderMock = $this->createMock(Loader::class);
            $this->directoryListMock = $this->createMock(DirectoryList::class);
            $this->adminFactoryMock = $this->createMock(AdminAccountFactory::class);
            $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
            $this->randomMock = $this->createMock(Random::class);
            $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
            $this->maintenanceModeMock = $this->createMock(MaintenanceMode::class);
            $this->filesystemMock = $this->createMock(Filesystem::class);
            $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
            $this->contextMock = $this->createMock(Context::class);
            $this->configModelMock = $this->createMock(ConfigModel::class);
            $this->cleanupFilesMock = $this->createMock(CleanupFiles::class);
            $this->dbValidatorMock = $this->createMock(DbValidator::class);
            $this->setupFactoryMock = $this->createMock(SetupFactory::class);
            $this->dataSetupFactoryMock = $this->createMock(DataSetupFactory::class);
            $this->sampleDataStateMock = $this->createMock(State::class);
            $this->componentRegistrarMock = $this->createMock(ComponentRegistrar::class);
            $this->phpReadinessCheckMock = $this->createMock(PhpReadinessCheck::class);
            $this->declarationInstallerMock = $this->createMock(DeclarationInstaller::class);
            $this->schemaListenerMock = $this->createMock(SchemaListener::class);
            $this->patchApplierFactoryMock = $this->createMock(PatchApplierFactory::class);
            $this->patchApplierMock = $this->createMock(PatchApplier::class);
            $this->patchApplierFactoryMock->expects($this->any())
                ->method('create')
                ->willReturn($this->patchApplierMock);
            $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
            $this->object = $this->createObject();
        }

        /**
         * Instantiates the object with mocks
         * @param MockObject|bool $connectionFactoryMock
         * @param MockObject|bool $objectManagerProviderMock
         * @return Installer
         */
        private function createObject($connectionFactoryMock = false, $objectManagerProviderMock = false)
        {
            if (!$connectionFactoryMock) {
                $connectionFactoryMock = $this->createMock(ConnectionFactory::class);
                $connectionFactoryMock->expects($this->any())
                    ->method('create')
                    ->willReturn($this->connectionMock);
            }
            if (!$objectManagerProviderMock) {
                $objectManagerProviderMock = $this->createMock(ObjectManagerProvider::class);
                $objectManagerProviderMock->expects($this->any())
                    ->method('get')
                    ->willReturn($this->objectManager);
            }

            return (new ObjectManager($this))->getObject(
                Installer::class,
                [
                    'filePermissions' => $this->filePermissionsMock,
                    'deploymentConfigWriter' =>  $this->configWriterMock,
                    'deploymentConfigReader' =>  $this->configReaderMock,
                    'moduleList' =>  $this->moduleListMock,
                    'moduleLoader' =>  $this->moduleLoaderMock,
                    'adminAccountFactory' =>   $this->adminFactoryMock,
                    'log' =>  $this->loggerMock,
                    'connectionFactory' =>  $connectionFactoryMock,
                    'maintenanceMode' =>  $this->maintenanceModeMock,
                    'filesystem' =>  $this->filesystemMock,
                    [],
                    'deploymentConfig' => $this->configMock,
                    'objectManagerProvider' =>  $objectManagerProviderMock,
                    'context' =>   $this->contextMock,
                    'setupConfigModel' =>  $this->configModelMock,
                    'cleanupFiles' =>   $this->cleanupFilesMock,
                    'dbValidator' =>   $this->dbValidatorMock,
                    'setupFactory' =>  $this->setupFactoryMock,
                    'dataSetupFactory' =>   $this->dataSetupFactoryMock,
                    'sampleDataState' =>   $this->sampleDataStateMock,
                    'componentRegistrar' =>   $this->componentRegistrarMock,
                    'phpReadinessCheck' =>  $this->phpReadinessCheckMock
                ]
            );
        }

        /**
         * @param array $request
         * @param array $logMessages
         * @dataProvider installDataProvider
         * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
         */
        public function testInstall(array $request, array $logMessages): void
        {
            $this->configMock->expects($this->atLeastOnce())
                ->method('get')
                ->willReturnMap(
                    [
                        [ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT, null, true],
                        [ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY, null, true],
                        ['modules/Magento_User', null, '1']
                    ]
                );
            $allModules = ['Foo_One' => [], 'Bar_Two' => []];

            $this->declarationInstallerMock->expects($this->once())->method('installSchema');
            $this->moduleLoaderMock->expects($this->any())->method('load')->willReturn($allModules);
            $connectionMock = $this->getMockBuilder(AdapterInterface::class)
                ->setMethods(['getSchemaListener', 'newTable'])
                ->getMockForAbstractClass();
            $connectionMock->expects($this->any())->method('getSchemaListener')->willReturn($this->schemaListenerMock);

            $setupMock = $this->createMock(Setup::class);
            $setupMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);

            $tableMock = $this->createMock(Table::class);
            $tableMock->expects($this->any())->method('addColumn')->willReturn($tableMock);
            $tableMock->expects($this->any())->method('setComment')->willReturn($tableMock);
            $tableMock->expects($this->any())->method('addIndex')->willReturn($tableMock);

            $connectionMock->expects($this->any())->method('newTable')->willReturn($tableMock);

            $resourceMock = $this->createMock(ResourceConnection::class);
            $this->contextMock->expects($this->any())->method('getResources')->willReturn($resourceMock);
            $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);

            $dataSetupMock = $this->createMock(DataSetup::class);
            $dataSetupMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);

            $cacheManagerMock = $this->createMock(Manager::class);
            $cacheManagerMock->expects($this->any())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
            $cacheManagerMock->expects($this->exactly(3))->method('setEnabled')->willReturn(['foo', 'bar']);
            $cacheManagerMock->expects($this->exactly(3))->method('clean');
            $cacheManagerMock->expects($this->exactly(3))->method('getStatus')->willReturn(['foo' => 1, 'bar' => 1]);

            $appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
                ->disableOriginalConstructor()
                ->disableArgumentCloning()
                ->getMock();
            $appStateMock->expects($this->once())
                ->method('setAreaCode')
                ->with(Area::AREA_GLOBAL);

            $registryMock = $this->createMock(Registry::class);
            $searchConfigMock = $this->getMockBuilder(SearchConfig::class)->disableOriginalConstructor()->getMock();
            $this->setupFactoryMock->expects($this->atLeastOnce())
                ->method('create')
                ->with($resourceMock)
                ->willReturn($setupMock);
            $this->dataSetupFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($dataSetupMock);
            $this->objectManager->expects($this->any())
                ->method('create')
                ->willReturnMap([
                    [Manager::class, [], $cacheManagerMock],
                    [\Magento\Framework\App\State::class, [], $appStateMock],
                    [
                        PatchApplierFactory::class,
                        ['objectManager' => $this->objectManager],
                        $this->patchApplierFactoryMock
                    ],
                ]);
            $this->patchApplierMock->expects($this->exactly(2))
                ->method('applySchemaPatch')
                ->willReturnMap(
                    [
                        ['Bar_Two'],
                        ['Foo_One'],
                    ]
                );
            $this->patchApplierMock->expects($this->exactly(2))
                ->method('applyDataPatch')
                ->willReturnMap(
                    [
                        ['Bar_Two'],
                        ['Foo_One'],
                    ]
                );
            $this->objectManager->expects($this->any())
                ->method('get')
                ->willReturnMap([
                    [\Magento\Framework\App\State::class, $appStateMock],
                    [Manager::class, $cacheManagerMock],
                    [DeclarationInstaller::class, $this->declarationInstallerMock],
                    [Registry::class, $registryMock],
                    [SearchConfig::class, $searchConfigMock]
                ]);
            $this->adminFactoryMock->expects($this->any())->method('create')->willReturn(
                $this->createMock(AdminAccount::class)
            );
            $this->sampleDataStateMock->expects($this->once())->method('hasError')->willReturn(true);
            $this->phpReadinessCheckMock->expects($this->once())->method('checkPhpExtensions')->willReturn(
                ['responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS]
            );
            $this->filePermissionsMock->expects($this->any())
                ->method('getMissingWritablePathsForInstallation')
                ->willReturn([]);
            $this->filePermissionsMock->expects($this->once())
                ->method('getMissingWritableDirectoriesForDbUpgrade')
                ->willReturn([]);
            call_user_func_array(
                [
                    $this->loggerMock->expects($this->exactly(count($logMessages)))->method('log'),
                    'withConsecutive'
                ],
                $logMessages
            );
            $this->loggerMock->expects($this->exactly(2))
                ->method('logSuccess')
                ->withConsecutive(
                    ['Magento installation complete.'],
                    ['Magento Admin URI: /']
                );

            $this->object->install($request);
        }

        /**
         * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
         */
        public function installDataProvider(): array
        {
            return [
                [
                    'request' => [
                        ConfigOptionsListConstants::INPUT_KEY_DB_HOST => '127.0.0.1',
                        ConfigOptionsListConstants::INPUT_KEY_DB_NAME => 'magento',
                        ConfigOptionsListConstants::INPUT_KEY_DB_USER => 'magento',
                        ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY => 'encryption_key',
                        ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'backend',
                    ],
                    'logMessages' => [
                        ['Starting Magento installation:'],
                        ['File permissions check...'],
                        ['Required extensions check...'],
                        ['Enabling Maintenance Mode...'],
                        ['Installing deployment configuration...'],
                        ['Installing database schema:'],
                        ['Schema creation/updates:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Schema post-updates:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Installing search configuration...'],
                        ['Installing user configuration...'],
                        ['Enabling caches:'],
                        ['Current status:'],
                        ['foo: 1'],
                        ['bar: 1'],
                        ['Installing data...'],
                        ['Data install/update:'],
                        ['Disabling caches:'],
                        ['Current status:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Data post-updates:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Enabling caches:'],
                        ['Current status:'],
                        ['Caches clearing:'],
                        ['Cache cleared successfully'],
                        ['Disabling Maintenance Mode:'],
                        ['Post installation file permissions check...'],
                        ['Write installation date...'],
                        ['Sample Data is installed with errors. See log file for details']
                    ],
                ],
                [
                    'request' => [
                        ConfigOptionsListConstants::INPUT_KEY_DB_HOST => '127.0.0.1',
                        ConfigOptionsListConstants::INPUT_KEY_DB_NAME => 'magento',
                        ConfigOptionsListConstants::INPUT_KEY_DB_USER => 'magento',
                        ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY => 'encryption_key',
                        ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'backend',
                        AdminAccount::KEY_USER => 'admin',
                        AdminAccount::KEY_PASSWORD => '123',
                        AdminAccount::KEY_EMAIL => 'admin@example.com',
                        AdminAccount::KEY_FIRST_NAME => 'John',
                        AdminAccount::KEY_LAST_NAME => 'Doe',
                    ],
                    'logMessages' => [
                        ['Starting Magento installation:'],
                        ['File permissions check...'],
                        ['Required extensions check...'],
                        ['Enabling Maintenance Mode...'],
                        ['Installing deployment configuration...'],
                        ['Installing database schema:'],
                        ['Schema creation/updates:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Schema post-updates:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Installing search configuration...'],
                        ['Installing user configuration...'],
                        ['Enabling caches:'],
                        ['Current status:'],
                        ['foo: 1'],
                        ['bar: 1'],
                        ['Installing data...'],
                        ['Data install/update:'],
                        ['Disabling caches:'],
                        ['Current status:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Data post-updates:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Enabling caches:'],
                        ['Current status:'],
                        ['Installing admin user...'],
                        ['Caches clearing:'],
                        ['Cache cleared successfully'],
                        ['Disabling Maintenance Mode:'],
                        ['Post installation file permissions check...'],
                        ['Write installation date...'],
                        ['Sample Data is installed with errors. See log file for details']
                    ],
                ],
            ];
        }

        public function testCheckInstallationFilePermissions(): void
        {
            $this->filePermissionsMock
                ->expects($this->once())
                ->method('getMissingWritablePathsForInstallation')
                ->willReturn([]);
            $this->object->checkInstallationFilePermissions();
        }

        public function testCheckInstallationFilePermissionsError(): void
        {
            $this->expectException('Exception');
            $this->expectExceptionMessage('Missing write permissions to the following paths:');
            $this->filePermissionsMock
                ->expects($this->once())
                ->method('getMissingWritablePathsForInstallation')
                ->willReturn(['foo', 'bar']);
            $this->object->checkInstallationFilePermissions();
        }

        public function testCheckExtensions(): void
        {
            $this->phpReadinessCheckMock->expects($this->once())->method('checkPhpExtensions')->willReturn(
                ['responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS]
            );
            $this->object->checkExtensions();
        }

        public function testCheckExtensionsError(): void
        {
            $this->expectException('Exception');
            $this->expectExceptionMessage('Missing following extensions: \'foo\'');
            $this->phpReadinessCheckMock->expects($this->once())->method('checkPhpExtensions')->willReturn(
                [
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                    'data' => ['required' => ['foo', 'bar'], 'missing' => ['foo']]
                ]
            );
            $this->object->checkExtensions();
        }

        public function testCheckApplicationFilePermissions(): void
        {
            $this->filePermissionsMock
                ->expects($this->once())
                ->method('getUnnecessaryWritableDirectoriesForApplication')
                ->willReturn(['foo', 'bar']);
            $expectedMessage = "For security, remove write permissions from these directories: 'foo' 'bar'";
            $this->loggerMock->expects($this->once())->method('log')->with($expectedMessage);
            $this->object->checkApplicationFilePermissions();
            $this->assertSame(['message' => [$expectedMessage]], $this->object->getInstallInfo());
        }

        public function testUpdateModulesSequence(): void
        {
            $this->cleanupFilesMock->expects($this->once())->method('clearCodeGeneratedFiles')->willReturn(
                [
                    "The directory '/generation' doesn't exist - skipping cleanup",
                ]
            );
            $installer = $this->prepareForUpdateModulesTests();

            $this->loggerMock->expects($this->at(0))->method('log')->with('Cache cleared successfully');
            $this->loggerMock->expects($this->at(1))->method('log')->with('File system cleanup:');
            $this->loggerMock->expects($this->at(2))->method('log')
                ->with('The directory \'/generation\' doesn\'t exist - skipping cleanup');
            $this->loggerMock->expects($this->at(3))->method('log')->with('Updating modules:');
            $installer->updateModulesSequence(false);
        }

        public function testUpdateModulesSequenceKeepGenerated(): void
        {
            $this->cleanupFilesMock->expects($this->never())->method('clearCodeGeneratedClasses');

            $installer = $this->prepareForUpdateModulesTests();

            $this->loggerMock->expects($this->at(0))->method('log')->with('Cache cleared successfully');
            $this->loggerMock->expects($this->at(1))->method('log')->with('Updating modules:');
            $installer->updateModulesSequence(true);
        }

        public function testUninstall(): void
        {
            $this->configMock->expects($this->once())
                ->method('get')
                ->with(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS)
                ->willReturn([]);
            $this->configReaderMock->expects($this->once())->method('getFiles')->willReturn([
                'ConfigOne.php',
                'ConfigTwo.php'
            ]);
            $configDir = $this->getMockForAbstractClass(
                WriteInterface::class
            );
            $configDir
                ->expects($this->exactly(2))
                ->method('getAbsolutePath')
                ->willReturnMap(
                    [
                        ['ConfigOne.php', '/config/ConfigOne.php'],
                        ['ConfigTwo.php', '/config/ConfigTwo.php']
                    ]
                );
            $this->filesystemMock
                ->expects($this->any())
                ->method('getDirectoryWrite')
                ->willReturnMap([
                    [DirectoryList::CONFIG, DriverPool::FILE, $configDir],
                ]);
            $this->loggerMock->expects($this->at(0))->method('log')->with('Starting Magento uninstallation:');
            $this->loggerMock
                ->expects($this->at(2))
                ->method('log')
                ->with('No database connection defined - skipping database cleanup');
            $cacheManager = $this->createMock(Manager::class);
            $cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
            $cacheManager->expects($this->once())->method('clean');
            $this->objectManager->expects($this->any())
                ->method('get')
                ->with(Manager::class)
                ->willReturn($cacheManager);
            $this->loggerMock->expects($this->at(1))->method('log')->with('Cache cleared successfully');
            $this->loggerMock->expects($this->at(3))->method('log')->with('File system cleanup:');
            $this->loggerMock
                ->expects($this->at(4))
                ->method('log')
                ->with("The directory '/var' doesn't exist - skipping cleanup");
            $this->loggerMock
                ->expects($this->at(5))
                ->method('log')
                ->with("The directory '/static' doesn't exist - skipping cleanup");
            $this->loggerMock
                ->expects($this->at(6))
                ->method('log')
                ->with("The file '/config/ConfigOne.php' doesn't exist - skipping cleanup");
            $this->loggerMock
                ->expects($this->at(7))
                ->method('log')
                ->with("The file '/config/ConfigTwo.php' doesn't exist - skipping cleanup");
            $this->loggerMock->expects($this->once())->method('logSuccess')->with('Magento uninstallation complete.');
            $this->cleanupFilesMock->expects($this->once())->method('clearAllFiles')->willReturn(
                [
                    "The directory '/var' doesn't exist - skipping cleanup",
                    "The directory '/static' doesn't exist - skipping cleanup"
                ]
            );

            $this->object->uninstall();
        }

        public function testCleanupDb(): void
        {
            $this->configMock->expects($this->once())
                ->method('get')
                ->with(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS)
                ->willReturn(self::$dbConfig);
            $this->connectionMock->expects($this->at(0))
                ->method('quoteIdentifier')
                ->with('magento')->willReturn(
                    '`magento`'
                );
            $this->connectionMock->expects($this->at(1))
                ->method('query')
                ->with('DROP DATABASE IF EXISTS `magento`');
            $this->connectionMock->expects($this->at(2))
                ->method('query')
                ->with('CREATE DATABASE IF NOT EXISTS `magento`');
            $this->loggerMock->expects($this->once())
                ->method('log')
                ->with('Cleaning up database `magento`');
            $this->object->cleanupDb();
        }

        /**
         * Prepare mocks for update modules tests and returns the installer to use
         * @return Installer
         */
        private function prepareForUpdateModulesTests()
        {
            $allModules = [
                'Foo_One' => [],
                'Bar_Two' => [],
                'New_Module' => [],
            ];

            $cacheManager = $this->createMock(Manager::class);
            $cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
            $cacheManager->expects($this->once())->method('clean');
            $this->objectManager->expects($this->any())
                ->method('get')
                ->willReturnMap([
                    [Manager::class, $cacheManager]
                ]);
            $this->moduleLoaderMock->expects($this->once())->method('load')->willReturn($allModules);

            $expectedModules = [
                ConfigFilePool::APP_CONFIG => [
                    'modules' => [
                        'Bar_Two' => 0,
                        'Foo_One' => 1,
                        'New_Module' => 1
                    ]
                ]
            ];

            $this->configMock->expects($this->atLeastOnce())
                ->method('get')
                ->with(ConfigOptionsListConstants::KEY_MODULES)
                ->willReturn(true);

            $newObject = $this->createObject(false, false);
            $this->configReaderMock->expects($this->once())->method('load')
                ->willReturn(['modules' => ['Bar_Two' => 0, 'Foo_One' => 1, 'Old_Module' => 0]]);
            $this->configWriterMock->expects($this->once())->method('saveConfig')->with($expectedModules);

            return $newObject;
        }
    }
}

namespace Magento\Setup\Model {

    /**
     * Mocking autoload function
     *
     * @returns array
     */
    function spl_autoload_functions()
    {
        return ['mock_function_one', 'mock_function_two'];
    }
}
