<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\ResourceConnection\Config;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\ModuleList\Loader as ModuleLoader;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Setup\FilePermissions;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\LoggerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Setup\Console\Command\InstallCommand;
use Magento\Setup\Controller\ResponseTypeInterface;
use Magento\Setup\Model\ConfigModel as SetupConfigModel;
use Magento\Setup\Module\ConnectionFactory;
use Magento\Setup\Module\DataSetupFactory;
use Magento\Setup\Module\Setup;
use Magento\Setup\Module\SetupFactory;
use Magento\Setup\Validator\DbValidator;
use Magento\Store\Model\Store;

/**
 * Class Installer contains the logic to install Magento application.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Installer
{
    /**#@+
     * Parameters for enabling/disabling modules
     */
    const ENABLE_MODULES = 'enable_modules';
    const DISABLE_MODULES = 'disable_modules';
    /**#@- */

    /**#@+
     * Formatting for progress log
     */
    const PROGRESS_LOG_RENDER = '[Progress: %d / %d]';
    const PROGRESS_LOG_REGEX = '/\[Progress: (\d+) \/ (\d+)\]/s';
    /**#@- */

    /**#@+
     * Instance types for schema and data handler
     */
    const SCHEMA_INSTALL = \Magento\Framework\Setup\InstallSchemaInterface::class;
    const SCHEMA_UPGRADE = \Magento\Framework\Setup\UpgradeSchemaInterface::class;
    const DATA_INSTALL = \Magento\Framework\Setup\InstallDataInterface::class;
    const DATA_UPGRADE = \Magento\Framework\Setup\UpgradeDataInterface::class;
    /**#@- */

    const INFO_MESSAGE = 'message';

    /**
     * The lowest supported MySQL verion
     */
    const MYSQL_VERSION_REQUIRED = '5.6.0';

    /**
     * File permissions checker
     *
     * @var FilePermissions
     */
    private $filePermissions;

    /**
     * Deployment configuration repository
     *
     * @var Writer
     */
    private $deploymentConfigWriter;

    /**
     * Deployment configuration reader
     *
     * @var Reader
     */
    private $deploymentConfigReader;

    /**
     * Module list
     *
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * Module list loader
     *
     * @var ModuleLoader
     */
    private $moduleLoader;

    /**
     * Admin account factory
     *
     * @var AdminAccountFactory
     */
    private $adminAccountFactory;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private $log;

    /**
     * DB connection factory
     *
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * Progress indicator
     *
     * @var Installer\Progress
     */
    private $progress;

    /**
     * Maintenance mode handler
     *
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * Magento filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Installation information
     *
     * @var array
     */
    private $installInfo = [];

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var SetupConfigModel
     */
    private $setupConfigModel;

    /**
     * @var CleanupFiles
     */
    private $cleanupFiles;

    /**
     * @var DbValidator
     */
    private $dbValidator;

    /**
     * Factory to create \Magento\Setup\Module\Setup
     *
     * @var SetupFactory
     */
    private $setupFactory;

    /**
     * Factory to create \Magento\Setup\Module\DataSetup
     *
     * @var DataSetupFactory
     */
    private $dataSetupFactory;

    /**
     * @var \Magento\Framework\Setup\SampleData\State
     */
    protected $sampleDataState;

    /**
     * Component Registrar
     *
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var PhpReadinessCheck
     */
    private $phpReadinessCheck;

    /**
     * Constructor
     *
     * @param FilePermissions $filePermissions
     * @param Writer $deploymentConfigWriter
     * @param Reader $deploymentConfigReader
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param ModuleListInterface $moduleList
     * @param ModuleLoader $moduleLoader
     * @param AdminAccountFactory $adminAccountFactory
     * @param LoggerInterface $log
     * @param ConnectionFactory $connectionFactory
     * @param MaintenanceMode $maintenanceMode
     * @param Filesystem $filesystem
     * @param ObjectManagerProvider $objectManagerProvider
     * @param SetupConfigModel $setupConfigModel
     * @param CleanupFiles $cleanupFiles
     * @param DbValidator $dbValidator
     * @param SetupFactory $setupFactory
     * @param DataSetupFactory $dataSetupFactory
     * @param \Magento\Framework\Setup\SampleData\State $sampleDataState
     * @param ComponentRegistrar $componentRegistrar
     * @param PhpReadinessCheck $phpReadinessCheck
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FilePermissions $filePermissions,
        Writer $deploymentConfigWriter,
        Reader $deploymentConfigReader,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        ModuleListInterface $moduleList,
        ModuleLoader $moduleLoader,
        AdminAccountFactory $adminAccountFactory,
        LoggerInterface $log,
        ConnectionFactory $connectionFactory,
        MaintenanceMode $maintenanceMode,
        Filesystem $filesystem,
        ObjectManagerProvider $objectManagerProvider,
        SetupConfigModel $setupConfigModel,
        CleanupFiles $cleanupFiles,
        DbValidator $dbValidator,
        SetupFactory $setupFactory,
        DataSetupFactory $dataSetupFactory,
        \Magento\Framework\Setup\SampleData\State $sampleDataState,
        ComponentRegistrar $componentRegistrar,
        PhpReadinessCheck $phpReadinessCheck
    ) {
        $this->filePermissions = $filePermissions;
        $this->deploymentConfigWriter = $deploymentConfigWriter;
        $this->deploymentConfigReader = $deploymentConfigReader;
        $this->moduleList = $moduleList;
        $this->moduleLoader = $moduleLoader;
        $this->adminAccountFactory = $adminAccountFactory;
        $this->log = $log;
        $this->connectionFactory = $connectionFactory;
        $this->maintenanceMode = $maintenanceMode;
        $this->filesystem = $filesystem;
        $this->installInfo[self::INFO_MESSAGE] = [];
        $this->deploymentConfig = $deploymentConfig;
        $this->objectManagerProvider = $objectManagerProvider;
        $this->setupConfigModel = $setupConfigModel;
        $this->cleanupFiles = $cleanupFiles;
        $this->dbValidator = $dbValidator;
        $this->setupFactory = $setupFactory;
        $this->dataSetupFactory = $dataSetupFactory;
        $this->sampleDataState = $sampleDataState;
        $this->componentRegistrar = $componentRegistrar;
        $this->phpReadinessCheck = $phpReadinessCheck;
    }

    /**
     * Install Magento application
     *
     * @param \ArrayObject|array $request
     * @return void
     * @throws \LogicException
     */
    public function install($request)
    {
        $script[] = ['File permissions check...', 'checkInstallationFilePermissions', []];
        $script[] = ['Required extensions check...', 'checkExtensions', []];
        $script[] = ['Enabling Maintenance Mode...', 'setMaintenanceMode', [1]];
        $script[] = ['Installing deployment configuration...', 'installDeploymentConfig', [$request]];
        if (!empty($request[InstallCommand::INPUT_KEY_CLEANUP_DB])) {
            $script[] = ['Cleaning up database...', 'cleanupDb', []];
        }
        $script[] = ['Installing database schema:', 'installSchema', []];
        $script[] = ['Installing user configuration...', 'installUserConfig', [$request]];
        $script[] = ['Enabling caches:', 'enableCaches', []];
        $script[] = ['Installing data...', 'installDataFixtures', []];
        if (!empty($request[InstallCommand::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX])) {
            $script[] = [
                'Creating sales order increment prefix...',
                'installOrderIncrementPrefix',
                [$request[InstallCommand::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX]],
            ];
        }
        $script[] = ['Installing admin user...', 'installAdminUser', [$request]];
        $script[] = ['Caches clearing:', 'cleanCaches', []];
        $script[] = ['Disabling Maintenance Mode:', 'setMaintenanceMode', [0]];
        $script[] = ['Post installation file permissions check...', 'checkApplicationFilePermissions', []];
        $script[] = ['Write installation date...', 'writeInstallationDate', []];

        $estimatedModules = $this->createModulesConfig($request, true);
        $total = count($script) + 4 * count(array_filter($estimatedModules));
        $this->progress = new Installer\Progress($total, 0);

        $this->log->log('Starting Magento installation:');

        foreach ($script as $item) {
            list($message, $method, $params) = $item;
            $this->log->log($message);
            call_user_func_array([$this, $method], $params);
            $this->logProgress();
        }

        $this->log->logSuccess('Magento installation complete.');
        $this->log->logSuccess(
            'Magento Admin URI: /'
            . $this->deploymentConfig->get(BackendConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME)
        );

        if ($this->progress->getCurrent() != $this->progress->getTotal()) {
            throw new \LogicException('Installation progress did not finish properly.');
        }
        if ($this->sampleDataState->hasError()) {
            $this->log->log('Sample Data is installed with errors. See log file for details');
        }
    }

    /**
     * Writes installation date to the configuration
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Called by install() via callback.
     */
    private function writeInstallationDate()
    {
        $dateData = new ConfigData(ConfigFilePool::APP_ENV);
        $dateData->set(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE, date('r'));
        $configData = [$dateData->getFileKey() => $dateData->getData()];
        $this->deploymentConfigWriter->saveConfig($configData);
    }

    /**
     * Creates modules deployment configuration segment
     *
     * @param \ArrayObject|array $request
     * @param bool $dryRun
     * @return array
     * @throws \LogicException
     */
    private function createModulesConfig($request, $dryRun = false)
    {
        $all = array_keys($this->moduleLoader->load());
        $deploymentConfig = $this->deploymentConfigReader->load();
        $currentModules = isset($deploymentConfig[ConfigOptionsListConstants::KEY_MODULES])
            ? $deploymentConfig[ConfigOptionsListConstants::KEY_MODULES] : [];
        $enable = $this->readListOfModules($all, $request, self::ENABLE_MODULES);
        $disable = $this->readListOfModules($all, $request, self::DISABLE_MODULES);
        $result = [];
        foreach ($all as $module) {
            if ((isset($currentModules[$module]) && !$currentModules[$module])) {
                $result[$module] = 0;
            } else {
                $result[$module] = 1;
            }
            if (in_array($module, $disable)) {
                $result[$module] = 0;
            }
            if (in_array($module, $enable)) {
                $result[$module] = 1;
            }
        }
        if (!$dryRun) {
            $this->deploymentConfigWriter->saveConfig([ConfigFilePool::APP_CONFIG => ['modules' => $result]], true);
        }
        return $result;
    }

    /**
     * Determines list of modules from request based on list of all modules
     *
     * @param string[] $all
     * @param array $request
     * @param string $key
     * @return string[]
     * @throws \LogicException
     */
    private function readListOfModules($all, $request, $key)
    {
        $result = [];
        if (!empty($request[$key])) {
            if ($request[$key] == 'all') {
                $result = $all;
            } else {
                $result = explode(',', $request[$key]);
                foreach ($result as $module) {
                    if (!in_array($module, $all)) {
                        throw new \LogicException("Unknown module in the requested list: '{$module}'");
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Logs progress
     *
     * @return void
     */
    private function logProgress()
    {
        if (!$this->progress) {
            return;
        }
        $this->progress->setNext();
        $this->log->logMeta(
            sprintf(self::PROGRESS_LOG_RENDER, $this->progress->getCurrent(), $this->progress->getTotal())
        );
    }

    /**
     * Check permissions of directories that are expected to be writable for installation
     *
     * @return void
     * @throws \Exception
     */
    public function checkInstallationFilePermissions()
    {
        $this->throwExceptionForNotWritablePaths(
            $this->filePermissions->getMissingWritablePathsForInstallation()
        );
    }

    /**
     * Check required extensions for installation
     *
     * @return void
     * @throws \Exception
     */
    public function checkExtensions()
    {
        $phpExtensionsCheckResult = $this->phpReadinessCheck->checkPhpExtensions();
        if ($phpExtensionsCheckResult['responseType'] === ResponseTypeInterface::RESPONSE_TYPE_ERROR
            && isset($phpExtensionsCheckResult['data']['missing'])
        ) {
            $errorMsg = "Missing following extensions: '"
                . implode("' '", $phpExtensionsCheckResult['data']['missing']) . "'";
            throw new \Exception($errorMsg);
        }
    }

    /**
     * Check permissions of directories that are expected to be non-writable for application
     *
     * @return void
     */
    public function checkApplicationFilePermissions()
    {
        $results = $this->filePermissions->getUnnecessaryWritableDirectoriesForApplication();
        if ($results) {
            $errorMsg = "For security, remove write permissions from these directories: '"
                . implode("' '", $results) . "'";
            $this->log->log($errorMsg);
            $this->installInfo[self::INFO_MESSAGE][] = $errorMsg;
        }
    }

    /**
     * Installs deployment configuration
     *
     * @param \ArrayObject|array $data
     * @return void
     */
    public function installDeploymentConfig($data)
    {
        $this->checkInstallationFilePermissions();
        $this->createModulesConfig($data);
        $userData = is_array($data) ? $data : $data->getArrayCopy();
        $this->setupConfigModel->process($userData);
        $deploymentConfigData = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        if (isset($deploymentConfigData)) {
            $this->installInfo[ConfigOptionsListConstants::KEY_ENCRYPTION_KEY] = $deploymentConfigData;
        }
        // reset object manager now that there is a deployment config
        $this->objectManagerProvider->reset();
    }

    /**
     * Set up setup_module table to register modules' versions, skip this process if it already exists
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function setupModuleRegistry(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        if (!$connection->isTableExists($setup->getTable('setup_module'))) {
            /**
             * Create table 'setup_module'
             */
            $table = $connection->newTable($setup->getTable('setup_module'))
                ->addColumn(
                    'module',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'primary' => true],
                    'Module'
                )->addColumn(
                    'schema_version',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [],
                    'Schema Version'
                )->addColumn(
                    'data_version',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [],
                    'Data Version'
                )->setComment('Module versions registry');
            $connection->createTable($table);
        }
    }

    /**
     * Set up core tables
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function setupCoreTables(SchemaSetupInterface $setup)
    {
        /* @var $connection \Magento\Framework\DB\Adapter\AdapterInterface */
        $connection = $setup->getConnection();

        $setup->startSetup();

        $this->setupSessionTable($setup, $connection);
        $this->setupCacheTable($setup, $connection);
        $this->setupCacheTagTable($setup, $connection);
        $this->setupFlagTable($setup, $connection);

        $setup->endSetup();
    }

    /**
     * Create table 'session'
     *
     * @param SchemaSetupInterface $setup
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return void
     */
    private function setupSessionTable(
        SchemaSetupInterface $setup,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection
    ) {
        if (!$connection->isTableExists($setup->getTable('session'))) {
            $table = $connection->newTable(
                $setup->getTable('session')
            )->addColumn(
                'session_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'primary' => true],
                'Session Id'
            )->addColumn(
                'session_expires',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Date of Session Expiration'
            )->addColumn(
                'session_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
                '2M',
                ['nullable' => false],
                'Session Data'
            )->setComment(
                'Database Sessions Storage'
            );
            $connection->createTable($table);
        }
    }

    /**
     * Create table 'cache'
     *
     * @param SchemaSetupInterface $setup
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return void
     */
    private function setupCacheTable(
        SchemaSetupInterface $setup,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection
    ) {
        if (!$connection->isTableExists($setup->getTable('cache'))) {
            $table = $connection->newTable(
                $setup->getTable('cache')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                ['nullable' => false, 'primary' => true],
                'Cache Id'
            )->addColumn(
                'data',
                \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
                '2M',
                [],
                'Cache Data'
            )->addColumn(
                'create_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Cache Creation Time'
            )->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Time of Cache Updating'
            )->addColumn(
                'expire_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Cache Expiration Time'
            )->addIndex(
                $setup->getIdxName('cache', ['expire_time']),
                ['expire_time']
            )->setComment(
                'Caches'
            );
            $connection->createTable($table);
        }
    }

    /**
     * Create table 'cache_tag'
     *
     * @param SchemaSetupInterface $setup
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return void
     */
    private function setupCacheTagTable(
        SchemaSetupInterface $setup,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection
    ) {
        if (!$connection->isTableExists($setup->getTable('cache_tag'))) {
            $table = $connection->newTable(
                $setup->getTable('cache_tag')
            )->addColumn(
                'tag',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'primary' => true],
                'Tag'
            )->addColumn(
                'cache_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                ['nullable' => false, 'primary' => true],
                'Cache Id'
            )->addIndex(
                $setup->getIdxName('cache_tag', ['cache_id']),
                ['cache_id']
            )->setComment(
                'Tag Caches'
            );
            $connection->createTable($table);
        }
    }

    /**
     * Create table 'flag'
     *
     * @param SchemaSetupInterface $setup
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return void
     */
    private function setupFlagTable(
        SchemaSetupInterface $setup,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection
    ) {
        if (!$connection->isTableExists($setup->getTable('flag'))) {
            $table = $connection->newTable(
                $setup->getTable('flag')
            )->addColumn(
                'flag_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Flag Id'
            )->addColumn(
                'flag_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Flag Code'
            )->addColumn(
                'state',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Flag State'
            )->addColumn(
                'flag_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Flag Data'
            )->addColumn(
                'last_update',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Date of Last Flag Update'
            )->addIndex(
                $setup->getIdxName('flag', ['last_update']),
                ['last_update']
            )->setComment(
                'Flag'
            );
            $connection->createTable($table);
        }
    }

    /**
     * Installs DB schema
     *
     * @return void
     */
    public function installSchema()
    {
        $this->assertDbConfigExists();
        $this->assertDbAccessible();
        $setup = $this->setupFactory->create();
        $this->setupModuleRegistry($setup);
        $this->setupCoreTables($setup);
        $this->log->log('Schema creation/updates:');
        $this->handleDBSchemaData($setup, 'schema');
    }

    /**
     * Installs data fixtures
     *
     * @return void
     * @throws \Exception
     */
    public function installDataFixtures()
    {
        $this->assertDbConfigExists();
        $this->assertDbAccessible();
        $setup = $this->dataSetupFactory->create();
        $this->checkFilePermissionsForDbUpgrade();
        $this->log->log('Data install/update:');
        $this->handleDBSchemaData($setup, 'data');
    }

    /**
     * Check permissions of directories that are expected to be writable for database upgrade
     *
     * @return void
     * @throws \Exception If some of the required directories isn't writable
     */
    public function checkFilePermissionsForDbUpgrade()
    {
        $this->throwExceptionForNotWritablePaths(
            $this->filePermissions->getMissingWritableDirectoriesForDbUpgrade()
        );
    }

    /**
     * Throws exception with appropriate message if given not empty array of paths that requires writing permission
     *
     * @param array $paths List of not writable paths
     * @return void
     * @throws \Exception If given not empty array of not writable paths
     */
    private function throwExceptionForNotWritablePaths(array $paths)
    {
        if ($paths) {
            $errorMsg = "Missing write permissions to the following paths:" . PHP_EOL . implode(PHP_EOL, $paths);
            throw new \Exception($errorMsg);
        }
    }

    /**
     * Handles database schema and data (install/upgrade/backup/uninstall etc)
     *
     * @param SchemaSetupInterface | ModuleDataSetupInterface $setup
     * @param string $type
     * @return void
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function handleDBSchemaData($setup, $type)
    {
        if (!(($type === 'schema') || ($type === 'data'))) {
            throw  new \Magento\Setup\Exception("Unsupported operation type $type is requested");
        }
        $resource = $this->objectManagerProvider->get()->create(\Magento\Framework\Module\ModuleResource::class);
        $verType = $type . '-version';
        $installType = $type . '-install';
        $upgradeType = $type . '-upgrade';
        $moduleNames = $this->moduleList->getNames();
        $moduleContextList = $this->generateListOfModuleContext($resource, $verType);
        foreach ($moduleNames as $moduleName) {
            $this->log->log("Module '{$moduleName}':");
            $configVer = $this->moduleList->getOne($moduleName)['setup_version'];
            $currentVersion = $moduleContextList[$moduleName]->getVersion();
            // Schema/Data is installed
            if ($currentVersion !== '') {
                $status = version_compare($configVer, $currentVersion);
                if ($status == \Magento\Framework\Setup\ModuleDataSetupInterface::VERSION_COMPARE_GREATER) {
                    $upgrader = $this->getSchemaDataHandler($moduleName, $upgradeType);
                    if ($upgrader) {
                        $this->log->logInline("Upgrading $type.. ");
                        $upgrader->upgrade($setup, $moduleContextList[$moduleName]);
                    }
                    if ($type === 'schema') {
                        $resource->setDbVersion($moduleName, $configVer);
                    } elseif ($type === 'data') {
                        $resource->setDataVersion($moduleName, $configVer);
                    }
                }
            } elseif ($configVer) {
                $installer = $this->getSchemaDataHandler($moduleName, $installType);
                if ($installer) {
                    $this->log->logInline("Installing $type... ");
                    $installer->install($setup, $moduleContextList[$moduleName]);
                }
                $upgrader = $this->getSchemaDataHandler($moduleName, $upgradeType);
                if ($upgrader) {
                    $this->log->logInline("Upgrading $type... ");
                    $upgrader->upgrade($setup, $moduleContextList[$moduleName]);
                }
                if ($type === 'schema') {
                    $resource->setDbVersion($moduleName, $configVer);
                } elseif ($type === 'data') {
                    $resource->setDataVersion($moduleName, $configVer);
                }
            }
            $this->logProgress();
        }

        if ($type === 'schema') {
            $this->log->log('Schema post-updates:');
            $handlerType = 'schema-recurring';
        } elseif ($type === 'data') {
            $this->log->log('Data post-updates:');
            $handlerType = 'data-recurring';
        }
        foreach ($moduleNames as $moduleName) {
            $this->log->log("Module '{$moduleName}':");
            $modulePostUpdater = $this->getSchemaDataHandler($moduleName, $handlerType);
            if ($modulePostUpdater) {
                $this->log->logInline('Running ' . str_replace('-', ' ', $handlerType) . '...');
                $modulePostUpdater->install($setup, $moduleContextList[$moduleName]);
            }
            $this->logProgress();
        }
    }

    /**
     * @return void
     * @throws \Magento\Setup\Exception
     */
    private function assertDbConfigExists()
    {
        $config = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT);
        if (!$config) {
            throw new \Magento\Setup\Exception(
                "Can't run this operation: configuration for DB connection is absent."
            );
        }
    }

    /**
     * Installs user configuration
     *
     * @param \ArrayObject|array $data
     * @return void
     */
    public function installUserConfig($data)
    {
        $userConfig = new StoreConfigurationDataMapper();
        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManagerProvider->get()->get(\Magento\Framework\App\State::class);
        $appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $configData = $userConfig->getConfigData($data);
        if (count($configData) === 0) {
            return;
        }

        /** @var \Magento\Config\Model\Config\Factory $configFactory */
        $configFactory = $this->objectManagerProvider->get()->create(\Magento\Config\Model\Config\Factory::class);
        foreach ($configData as $key => $val) {
            $configModel = $configFactory->create();
            $configModel->setDataByPath($key, $val);
            $configModel->save();
        }
    }

    /**
     * Creates data handler
     * @param string $className
     * @param string $interfaceName
     * @return mixed|null
     * @throws \Magento\Setup\Exception
     */
    protected function createSchemaDataHandler($className, $interfaceName)
    {
        if (class_exists($className)) {
            if (!is_subclass_of($className, $interfaceName) && $className !== $interfaceName) {
                throw  new \Magento\Setup\Exception($className . ' must implement \\' . $interfaceName);
            } else {
                return $this->objectManagerProvider->get()->create($className);
            }
        }
        return null;
    }

    /**
     * Creates store order increment prefix configuration
     *
     * @param string $orderIncrementPrefix Value to use for order increment prefix
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Called by install() via callback.
     */
    private function installOrderIncrementPrefix($orderIncrementPrefix)
    {
        $setup = $this->setupFactory->create();
        $dbConnection = $setup->getConnection();

        // get entity_type_id for order
        $select = $dbConnection->select()
            ->from($setup->getTable('eav_entity_type'), 'entity_type_id')
            ->where('entity_type_code = \'order\'');
        $entityTypeId = $dbConnection->fetchOne($select);

        // See if row already exists
        $incrementRow = $dbConnection->fetchRow(
            'SELECT * FROM ' . $setup->getTable('eav_entity_store') . ' WHERE entity_type_id = ? AND store_id = ?',
            [$entityTypeId, Store::DISTRO_STORE_ID]
        );

        if (!empty($incrementRow)) {
            // row exists, update it
            $entityStoreId = $incrementRow['entity_store_id'];
            $dbConnection->update(
                $setup->getTable('eav_entity_store'),
                ['increment_prefix' => $orderIncrementPrefix],
                ['entity_store_id' => $entityStoreId]
            );
        } else {
            // add a row to the store's eav table, setting the increment_prefix
            $rowData = [
                'entity_type_id' => $entityTypeId,
                'store_id' => Store::DISTRO_STORE_ID,
                'increment_prefix' => $orderIncrementPrefix,
            ];
            $dbConnection->insert($setup->getTable('eav_entity_store'), $rowData);
        }
    }

    /**
     * Creates admin account
     *
     * @param \ArrayObject|array $data
     * @return void
     */
    public function installAdminUser($data)
    {
        $this->assertDbConfigExists();
        $data += ['db-prefix' => $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX)];
        $setup = $this->setupFactory->create();
        $adminAccount = $this->adminAccountFactory->create($setup->getConnection(), (array)$data);
        $adminAccount->save();
    }

    /**
     * Updates modules in deployment configuration
     *
     * @param bool $keepGeneratedFiles Cleanup generated classes and view files and reset ObjectManager
     * @return void
     * @throws \Magento\Setup\Exception
     */
    public function updateModulesSequence($keepGeneratedFiles = false)
    {
        $config = $this->deploymentConfig->get(ConfigOptionsListConstants::KEY_MODULES);
        if (!$config) {
            throw new \Magento\Setup\Exception(
                "Can't run this operation: deployment configuration is absent."
                . " Run 'magento setup:config:set --help' for options."
            );
        }
        $this->cleanCaches();
        if (!$keepGeneratedFiles) {
            $this->cleanupGeneratedFiles();
        }
        $this->log->log('Updating modules:');
        $this->createModulesConfig([]);
    }

    /**
     * Uninstall Magento application
     *
     * @return void
     */
    public function uninstall()
    {
        $this->log->log('Starting Magento uninstallation:');

        try {
            $this->cleanCaches();
        } catch (\Exception $e) {
            $this->log->log(
                'Can\'t clear cache due to the following error: '
                . $e->getMessage() . PHP_EOL
                . 'To fully clean up your uninstallation, you must manually clear your cache.'
            );
        }

        $this->cleanupDb();

        $this->log->log('File system cleanup:');
        $messages = $this->cleanupFiles->clearAllFiles();
        foreach ($messages as $message) {
            $this->log->log($message);
        }

        $this->deleteDeploymentConfig();

        $this->log->logSuccess('Magento uninstallation complete.');
    }

    /**
     * Enables caches after installing application
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Called by install() via callback.
     */
    private function enableCaches()
    {
        /** @var \Magento\Framework\App\Cache\Manager $cacheManager */
        $cacheManager = $this->objectManagerProvider->get()->create(\Magento\Framework\App\Cache\Manager::class);
        $types = $cacheManager->getAvailableTypes();
        $enabledTypes = $cacheManager->setEnabled($types, true);
        $cacheManager->clean($enabledTypes);

        $this->log->log('Current status:');
        $this->log->log(print_r($cacheManager->getStatus(), true));
    }

    /**
     * Clean caches after installing application
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Called by install() via callback.
     */
    private function cleanCaches()
    {
        /** @var \Magento\Framework\App\Cache\Manager $cacheManager */
        $cacheManager = $this->objectManagerProvider->get()->get(\Magento\Framework\App\Cache\Manager::class);
        $types = $cacheManager->getAvailableTypes();
        $cacheManager->clean($types);
        $this->log->log('Cache cleared successfully');
    }

    /**
     * Enables or disables maintenance mode for Magento application
     *
     * @param int $value
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Called by install() via callback.
     */
    private function setMaintenanceMode($value)
    {
        $this->maintenanceMode->set($value);
    }

    /**
     * Return messages
     *
     * @return array
     */
    public function getInstallInfo()
    {
        return $this->installInfo;
    }

    /**
     * Deletes the database and creates it again
     *
     * @return void
     */
    public function cleanupDb()
    {
        $config = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT);
        if ($config) {
            try {
                $connection = $this->connectionFactory->create($config);
                if (!$connection) {
                    $this->log->log("Can't create connection to database - skipping database cleanup");
                }
            } catch (\Exception $e) {
                $this->log->log($e->getMessage() . ' - skipping database cleanup');
                return;
            }
            $dbName = $connection->quoteIdentifier($config[ConfigOptionsListConstants::KEY_NAME]);
            $this->log->log("Cleaning up database {$dbName}");
            $connection->query("DROP DATABASE IF EXISTS {$dbName}");
            $connection->query("CREATE DATABASE IF NOT EXISTS {$dbName}");
        } else {
            $this->log->log('No database connection defined - skipping database cleanup');
        }
    }

    /**
     * Removes deployment configuration
     *
     * @return void
     */
    private function deleteDeploymentConfig()
    {
        $configDir = $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG);
        $configFiles = $this->deploymentConfigReader->getFiles();
        foreach ($configFiles as $configFile) {
            $absolutePath = $configDir->getAbsolutePath($configFile);
            if (!$configDir->isFile($configFile)) {
                $this->log->log("The file '{$absolutePath}' doesn't exist - skipping cleanup");
                continue;
            }
            try {
                $this->log->log($absolutePath);
                $configDir->delete($configFile);
            } catch (FileSystemException $e) {
                $this->log->log($e->getMessage());
            }
        }
    }

    /**
     * Validates that MySQL is accessible and MySQL version is supported
     *
     * @return void
     */
    private function assertDbAccessible()
    {
        $this->dbValidator->checkDatabaseConnection(
            $this->deploymentConfig->get(
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_NAME
            ),
            $this->deploymentConfig->get(
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_HOST
            ),
            $this->deploymentConfig->get(
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_USER
            ),
            $this->deploymentConfig->get(
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_PASSWORD
            )
        );
        $prefix = $this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
            '/' . ConfigOptionsListConstants::KEY_PREFIX
        );
        if (null !== $prefix) {
            $this->dbValidator->checkDatabaseTablePrefix($prefix);
        }
    }

    /**
     * Get handler for schema or data install/upgrade/backup/uninstall etc.
     *
     * @param string $moduleName
     * @param string $type
     * @return InstallSchemaInterface | UpgradeSchemaInterface | InstallDataInterface | UpgradeDataInterface | null
     * @throws \Magento\Setup\Exception
     */
    private function getSchemaDataHandler($moduleName, $type)
    {
        $className = str_replace('_', '\\', $moduleName) . '\Setup';
        switch ($type) {
            case 'schema-install':
                $className .= '\InstallSchema';
                $interface = self::SCHEMA_INSTALL;
                break;
            case 'schema-upgrade':
                $className .= '\UpgradeSchema';
                $interface = self::SCHEMA_UPGRADE;
                break;
            case 'schema-recurring':
                $className .= '\Recurring';
                $interface = self::SCHEMA_INSTALL;
                break;
            case 'data-install':
                $className .= '\InstallData';
                $interface = self::DATA_INSTALL;
                break;
            case 'data-upgrade':
                $className .= '\UpgradeData';
                $interface = self::DATA_UPGRADE;
                break;
            case 'data-recurring':
                $className .= '\RecurringData';
                $interface = self::DATA_INSTALL;
                break;
            default:
                throw new \Magento\Setup\Exception("$className does not exist");
        }

        return $this->createSchemaDataHandler($className, $interface);
    }

    /**
     * Generates list of ModuleContext
     *
     * @param \Magento\Framework\Module\ModuleResource $resource
     * @param string $type
     * @return ModuleContext[]
     * @throws \Magento\Setup\Exception
     */
    private function generateListOfModuleContext($resource, $type)
    {
        $moduleContextList = [];
        foreach ($this->moduleList->getNames() as $moduleName) {
            if ($type === 'schema-version') {
                $dbVer = $resource->getDbVersion($moduleName);
            } elseif ($type === 'data-version') {
                $dbVer = $resource->getDataVersion($moduleName);
            } else {
                throw  new \Magento\Setup\Exception("Unsupported version type $type is requested");
            }
            if ($dbVer !== false) {
                $moduleContextList[$moduleName] = new ModuleContext($dbVer);
            } else {
                $moduleContextList[$moduleName] = new ModuleContext('');
            }
        }
        return $moduleContextList;
    }

    /**
     * Clear generated/code and reset object manager
     *
     * @return void
     */
    private function cleanupGeneratedFiles()
    {
        $this->log->log('File system cleanup:');
        $messages = $this->cleanupFiles->clearCodeGeneratedFiles();

        // unload Magento autoloader because it may be using compiled definition
        foreach (spl_autoload_functions() as $autoloader) {
            if (is_array($autoloader) && $autoloader[0] instanceof \Magento\Framework\Code\Generator\Autoloader) {
                spl_autoload_unregister([$autoloader[0], $autoloader[1]]);
                break;
            }
        }

        // Corrected Magento autoloader will be loaded upon next get() call on $this->objectManagerProvider
        $this->objectManagerProvider->reset();

        foreach ($messages as $message) {
            $this->log->log($message);
        }
    }
}
