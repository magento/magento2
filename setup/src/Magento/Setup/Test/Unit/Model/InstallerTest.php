<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model {

    use Magento\Backend\Setup\ConfigOptionsList;
    use Magento\Framework\Config\ConfigOptionsListConstants;
    use Magento\Framework\Setup\SchemaListener;
    use Magento\Setup\Model\AdminAccount;
    use Magento\Setup\Model\DeclarationInstaller;
    use Magento\Setup\Model\Installer;
    use Magento\Framework\App\Filesystem\DirectoryList;
    use Magento\Framework\Filesystem\DriverPool;
    use Magento\Framework\Config\File\ConfigFilePool;
    use Magento\Framework\App\State\CleanupFiles;
    use Magento\Framework\Setup\Patch\PatchApplier;
    use Magento\Framework\Setup\Patch\PatchApplierFactory;
    use Magento\Setup\Validator\DbValidator;

    /**
     * @SuppressWarnings(PHPMD.TooManyFields)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    class InstallerTest extends \PHPUnit\Framework\TestCase
    {
        /**
         * @var \Magento\Setup\Model\Installer
         */
        private $object;

        /**
         * @var \Magento\Framework\Setup\FilePermissions|\PHPUnit_Framework_MockObject_MockObject
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
         * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\PhpReadinessCheck
         */
        private $phpReadinessCheck;

        /**
         * @var \Magento\Framework\Setup\DeclarationInstaller|\PHPUnit_Framework_MockObject_MockObject
         */
        private $declarationInstallerMock;

        /**
         * @var SchemaListener|\PHPUnit_Framework_MockObject_MockObject
         */
        private $schemaListenerMock;

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

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject
         */
        private $contextMock;

        /**
         * @var PatchApplier|\PHPUnit_Framework_MockObject_MockObject
         */
        private $patchApplierMock;

        /**
         * @var PatchApplierFactory|\PHPUnit_Framework_MockObject_MockObject
         */
        private $patchApplierFactoryMock;

        protected function setUp()
        {
            $this->filePermissions = $this->createMock(\Magento\Framework\Setup\FilePermissions::class);
            $this->configWriter = $this->createMock(\Magento\Framework\App\DeploymentConfig\Writer::class);
            $this->configReader = $this->createMock(\Magento\Framework\App\DeploymentConfig\Reader::class);
            $this->config = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);

            $this->moduleList = $this->getMockForAbstractClass(\Magento\Framework\Module\ModuleListInterface::class);
            $this->moduleList->expects($this->any())->method('getOne')->willReturn(
                ['setup_version' => '2.0.0']
            );
            $this->moduleList->expects($this->any())->method('getNames')->willReturn(
                ['Foo_One', 'Bar_Two']
            );
            $this->moduleLoader = $this->createMock(\Magento\Framework\Module\ModuleList\Loader::class);
            $this->directoryList =
                $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);
            $this->adminFactory = $this->createMock(\Magento\Setup\Model\AdminAccountFactory::class);
            $this->logger = $this->getMockForAbstractClass(\Magento\Framework\Setup\LoggerInterface::class);
            $this->random = $this->createMock(\Magento\Framework\Math\Random::class);
            $this->connection = $this->getMockForAbstractClass(\Magento\Framework\DB\Adapter\AdapterInterface::class);
            $this->maintenanceMode = $this->createMock(\Magento\Framework\App\MaintenanceMode::class);
            $this->filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
            $this->objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
            $this->contextMock =
                $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
            $this->configModel = $this->createMock(\Magento\Setup\Model\ConfigModel::class);
            $this->cleanupFiles = $this->createMock(\Magento\Framework\App\State\CleanupFiles::class);
            $this->dbValidator = $this->createMock(\Magento\Setup\Validator\DbValidator::class);
            $this->setupFactory = $this->createMock(\Magento\Setup\Module\SetupFactory::class);
            $this->dataSetupFactory = $this->createMock(\Magento\Setup\Module\DataSetupFactory::class);
            $this->sampleDataState = $this->createMock(\Magento\Framework\Setup\SampleData\State::class);
            $this->componentRegistrar =
                $this->createMock(\Magento\Framework\Component\ComponentRegistrar::class);
            $this->phpReadinessCheck = $this->createMock(\Magento\Setup\Model\PhpReadinessCheck::class);
            $this->declarationInstallerMock = $this->createMock(DeclarationInstaller::class);
            $this->schemaListenerMock = $this->createMock(SchemaListener::class);
            $this->patchApplierFactoryMock = $this->createMock(PatchApplierFactory::class);
            $this->patchApplierMock = $this->createMock(PatchApplier::class);
            $this->patchApplierFactoryMock->expects($this->any())->method('create')->willReturn(
                $this->patchApplierMock
            );
            $this->object = $this->createObject();
        }

        /**
         * Instantiates the object with mocks
         * @param \PHPUnit_Framework_MockObject_MockObject|bool $connectionFactory
         * @param \PHPUnit_Framework_MockObject_MockObject|bool $objectManagerProvider
         * @return Installer
         */
        private function createObject($connectionFactory = false, $objectManagerProvider = false)
        {
            if (!$connectionFactory) {
                $connectionFactory = $this->createMock(\Magento\Setup\Module\ConnectionFactory::class);
                $connectionFactory->expects($this->any())->method('create')->willReturn($this->connection);
            }
            if (!$objectManagerProvider) {
                $objectManagerProvider =
                    $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
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
                $this->componentRegistrar,
                $this->phpReadinessCheck,
                $this->declarationInstallerMock
            );
        }

        /**
         * @param array $request
         * @param array $logMessages
         * @dataProvider installDataProvider
         * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
         */
        public function testInstall(array $request, array $logMessages)
        {
            $this->config->expects($this->atLeastOnce())
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
            $this->moduleLoader->expects($this->any())->method('load')->willReturn($allModules);
            $setup = $this->createMock(\Magento\Setup\Module\Setup::class);
            $table = $this->createMock(\Magento\Framework\DB\Ddl\Table::class);
            $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
                ->setMethods(['getSchemaListener', 'newTable'])
                ->getMockForAbstractClass();
            $connection->expects($this->any())->method('getSchemaListener')->willReturn($this->schemaListenerMock);
            $setup->expects($this->any())->method('getConnection')->willReturn($connection);
            $table->expects($this->any())->method('addColumn')->willReturn($table);
            $table->expects($this->any())->method('setComment')->willReturn($table);
            $table->expects($this->any())->method('addIndex')->willReturn($table);
            $connection->expects($this->any())->method('newTable')->willReturn($table);
            $resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
            $this->contextMock->expects($this->any())->method('getResources')->willReturn($resource);
            $resource->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
            $dataSetup = $this->createMock(\Magento\Setup\Module\DataSetup::class);
            $dataSetup->expects($this->any())->method('getConnection')->willReturn($connection);
            $cacheManager = $this->createMock(\Magento\Framework\App\Cache\Manager::class);
            $cacheManager->expects($this->any())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
            $cacheManager->expects($this->exactly(3))->method('setEnabled')->willReturn(['foo', 'bar']);
            $cacheManager->expects($this->exactly(3))->method('clean');
            $cacheManager->expects($this->exactly(3))->method('getStatus')->willReturn(['foo' => 1, 'bar' => 1]);
            $appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
                ->disableOriginalConstructor()
                ->disableArgumentCloning()
                ->getMock();
            $appState->expects($this->once())
                ->method('setAreaCode')
                ->with(\Magento\Framework\App\Area::AREA_GLOBAL);
            $registry = $this->createMock(\Magento\Framework\Registry::class);
            $this->setupFactory->expects($this->atLeastOnce())->method('create')->with($resource)->willReturn($setup);
            $this->dataSetupFactory->expects($this->atLeastOnce())->method('create')->willReturn($dataSetup);
            $this->objectManager->expects($this->any())
                ->method('create')
                ->will($this->returnValueMap([
                    [\Magento\Framework\App\Cache\Manager::class, [], $cacheManager],
                    [\Magento\Framework\App\State::class, [], $appState],
                    [
                        PatchApplierFactory::class,
                        ['objectManager' => $this->objectManager],
                        $this->patchApplierFactoryMock
                    ],
                ]));
            $this->patchApplierMock->expects($this->exactly(2))->method('applySchemaPatch')->willReturnMap(
                [
                    ['Bar_Two'],
                    ['Foo_One'],
                ]
            );
            $this->patchApplierMock->expects($this->exactly(2))->method('applyDataPatch')->willReturnMap(
                [
                    ['Bar_Two'],
                    ['Foo_One'],
                ]
            );
            $this->objectManager->expects($this->any())
                ->method('get')
                ->will($this->returnValueMap([
                    [\Magento\Framework\App\State::class, $appState],
                    [\Magento\Framework\App\Cache\Manager::class, $cacheManager],
                    [\Magento\Setup\Model\DeclarationInstaller::class, $this->declarationInstallerMock],
                    [\Magento\Framework\Registry::class, $registry]
                ]));
            $this->adminFactory->expects($this->any())->method('create')->willReturn(
                $this->createMock(\Magento\Setup\Model\AdminAccount::class)
            );
            $this->sampleDataState->expects($this->once())->method('hasError')->willReturn(true);
            $this->phpReadinessCheck->expects($this->once())->method('checkPhpExtensions')->willReturn(
                ['responseType' => \Magento\Setup\Controller\ResponseTypeInterface::RESPONSE_TYPE_SUCCESS]
            );
            $this->filePermissions->expects($this->any())
                ->method('getMissingWritablePathsForInstallation')
                ->willReturn([]);
            $this->filePermissions->expects($this->once())
                ->method('getMissingWritableDirectoriesForDbUpgrade')
                ->willReturn([]);
            call_user_func_array(
                [
                    $this->logger->expects($this->exactly(count($logMessages)))->method('log'),
                    'withConsecutive'
                ],
                $logMessages
            );
            $this->logger->expects($this->exactly(2))
                ->method('logSuccess')
                ->withConsecutive(
                    ['Magento installation complete.'],
                    ['Magento Admin URI: /']
                );

            $this->object->install($request);
        }

        /**
         * @return array
         * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
         */
        public function installDataProvider()
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
                        ['Installing user configuration...'],
                        ['Enabling caches:'],
                        ['Current status:'],
                        [print_r(['foo' => 1, 'bar' => 1], true)],
                        ['Installing data...'],
                        ['Data install/update:'],
                        ['Disabling caches:'],
                        ['Current status:'],
                        [print_r([], true)],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Data post-updates:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Enabling caches:'],
                        ['Current status:'],
                        [print_r([], true)],
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
                        ['Installing user configuration...'],
                        ['Enabling caches:'],
                        ['Current status:'],
                        [print_r(['foo' => 1, 'bar' => 1], true)],
                        ['Installing data...'],
                        ['Data install/update:'],
                        ['Disabling caches:'],
                        ['Current status:'],
                        [print_r([], true)],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Data post-updates:'],
                        ['Module \'Foo_One\':'],
                        ['Module \'Bar_Two\':'],
                        ['Enabling caches:'],
                        ['Current status:'],
                        [print_r([], true)],
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

        public function testCheckInstallationFilePermissions()
        {
            $this->filePermissions
                ->expects($this->once())
                ->method('getMissingWritablePathsForInstallation')
                ->willReturn([]);
            $this->object->checkInstallationFilePermissions();
        }

        /**
         * @expectedException \Exception
         * @expectedExceptionMessage Missing write permissions to the following paths:
         */
        public function testCheckInstallationFilePermissionsError()
        {
            $this->filePermissions
                ->expects($this->once())
                ->method('getMissingWritablePathsForInstallation')
                ->willReturn(['foo', 'bar']);
            $this->object->checkInstallationFilePermissions();
        }

        public function testCheckExtensions()
        {
            $this->phpReadinessCheck->expects($this->once())->method('checkPhpExtensions')->willReturn(
                ['responseType' => \Magento\Setup\Controller\ResponseTypeInterface::RESPONSE_TYPE_SUCCESS]
            );
            $this->object->checkExtensions();
        }

        /**
         * @expectedException \Exception
         * @expectedExceptionMessage Missing following extensions: 'foo'
         */
        public function testCheckExtensionsError()
        {
            $this->phpReadinessCheck->expects($this->once())->method('checkPhpExtensions')->willReturn(
                [
                    'responseType' => \Magento\Setup\Controller\ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                    'data' => ['required' => ['foo', 'bar'], 'missing' => ['foo']]
                ]
            );
            $this->object->checkExtensions();
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
            $this->cleanupFiles->expects($this->once())->method('clearCodeGeneratedFiles')->will(
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
            $this->config->expects($this->once())
                ->method('get')
                ->with(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS)
                ->willReturn([]);
            $this->configReader->expects($this->once())->method('getFiles')->willReturn([
                'ConfigOne.php',
                'ConfigTwo.php'
            ]);
            $configDir = $this->getMockForAbstractClass(
                \Magento\Framework\Filesystem\Directory\WriteInterface::class
            );
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
            $cacheManager = $this->createMock(\Magento\Framework\App\Cache\Manager::class);
            $cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
            $cacheManager->expects($this->once())->method('clean');
            $this->objectManager->expects($this->any())
                ->method('get')
                ->with(\Magento\Framework\App\Cache\Manager::class)
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
                ->with(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS)
                ->willReturn(self::$dbConfig);
            $this->connection->expects($this->at(0))->method('quoteIdentifier')->with('magento')->willReturn(
                '`magento`'
            );
            $this->connection->expects($this->at(1))->method('query')->with('DROP DATABASE IF EXISTS `magento`');
            $this->connection->expects($this->at(2))->method('query')->with('CREATE DATABASE IF NOT EXISTS `magento`');
            $this->logger->expects($this->once())->method('log')->with('Cleaning up database `magento`');
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

            $cacheManager = $this->createMock(\Magento\Framework\App\Cache\Manager::class);
            $cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['foo', 'bar']);
            $cacheManager->expects($this->once())->method('clean');
            $this->objectManager->expects($this->any())
                ->method('get')
                ->will($this->returnValueMap([
                    [\Magento\Framework\App\Cache\Manager::class, $cacheManager]
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
                ->willReturn(['modules' => ['Bar_Two' => 0, 'Foo_One' => 1, 'Old_Module' => 0]]);
            $this->configWriter->expects($this->once())->method('saveConfig')->with($expectedModules);

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
