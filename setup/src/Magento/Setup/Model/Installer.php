<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\DeploymentConfig\BackendConfig;
use Magento\Framework\App\DeploymentConfig\DbConfig;
use Magento\Framework\App\DeploymentConfig\EncryptConfig;
use Magento\Framework\App\DeploymentConfig\InstallConfig;
use Magento\Framework\App\DeploymentConfig\ResourceConfig;
use Magento\Framework\App\DeploymentConfig\SessionConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\FilesystemException;
use Magento\Framework\Math\Random;
use Magento\Framework\Module\ModuleList\DeploymentConfig;
use Magento\Framework\Module\ModuleList\DeploymentConfigFactory;
use Magento\Framework\Module\ModuleList\Loader as ModuleLoader;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRenderer;
use Magento\Setup\Module\ConnectionFactory;
use Magento\Setup\Module\Setup;
use Magento\Store\Model\Store;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Model\Resource\Db\Context;

/**
 * Class Installer contains the logic to install Magento application.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Installer
{
    /**
     * Parameter indicating command whether to cleanup database in the install routine
     */
    const CLEANUP_DB = 'cleanup_database';

    /**#@+
     * Parameters for enabling/disabling modules
     */
    const ENABLE_MODULES = 'enable_modules';
    const DISABLE_MODULES = 'disable_modules';
    /**#@- */

    /**
     * Parameter indicating command whether to install Sample Data
     */
    const USE_SAMPLE_DATA = 'use_sample_data';

    /**
     * Parameter to specify an order_increment_prefix
     */
    const SALES_ORDER_INCREMENT_PREFIX = 'sales_order_increment_prefix';

    /**#@+
     * Formatting for progress log
     */
    const PROGRESS_LOG_RENDER = '[Progress: %d / %d]';
    const PROGRESS_LOG_REGEX = '/\[Progress: (\d+) \/ (\d+)\]/s';
    /**#@- */

    /**#@+
     * Instance types for schema and data handler
     */
    const SCHEMA_INSTALL = 'Magento\Framework\Setup\InstallSchemaInterface';
    const SCHEMA_UPGRADE = 'Magento\Framework\Setup\UpgradeSchemaInterface';
    const DATA_INSTALL = 'Magento\Framework\Setup\InstallDataInterface';
    const DATA_UPGRADE = 'Magento\Framework\Setup\UpgradeDataInterface';
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
     * @var Writer
     */
    private $deploymentConfigReader;

    /**
     * Module list
     *
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * Factory for module deployment config
     *
     * @var DeploymentConfigFactory
     */
    private $deploymentConfigFactory;

    /**
     * Module list loader
     *
     * @var ModuleLoader
     */
    private $moduleLoader;

    /**
     * List of directories of Magento application
     *
     * @var DirectoryList
     */
    private $directoryList;

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
     * Random Generator
     *
     * @var Random
     */
    private $random;

    /**
     * DB connection factory
     *
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * Shell command renderer
     *
     * @var CommandRenderer
     */
    private $shellRenderer;

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
     * @var SampleData
     */
    private $sampleData;

    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var Context
     */
    private $context;

    /**
     * Constructor
     *
     * @param FilePermissions $filePermissions
     * @param Writer $deploymentConfigWriter
     * @param Reader $deploymentConfigReader
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param DeploymentConfigFactory $deploymentConfigFactory
     * @param ModuleListInterface $moduleList
     * @param ModuleLoader $moduleLoader
     * @param DirectoryList $directoryList
     * @param AdminAccountFactory $adminAccountFactory
     * @param LoggerInterface $log
     * @param Random $random
     * @param ConnectionFactory $connectionFactory
     * @param MaintenanceMode $maintenanceMode
     * @param Filesystem $filesystem
     * @param SampleData $sampleData
     * @param ObjectManagerProvider $objectManagerProvider
     * @param Context $context
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FilePermissions $filePermissions,
        Writer $deploymentConfigWriter,
        Reader $deploymentConfigReader,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        DeploymentConfigFactory $deploymentConfigFactory,
        ModuleListInterface $moduleList,
        ModuleLoader $moduleLoader,
        DirectoryList $directoryList,
        AdminAccountFactory $adminAccountFactory,
        LoggerInterface $log,
        Random $random,
        ConnectionFactory $connectionFactory,
        MaintenanceMode $maintenanceMode,
        Filesystem $filesystem,
        SampleData $sampleData,
        ObjectManagerProvider $objectManagerProvider,
        Context $context
    ) {
        $this->filePermissions = $filePermissions;
        $this->deploymentConfigWriter = $deploymentConfigWriter;
        $this->deploymentConfigReader = $deploymentConfigReader;
        $this->deploymentConfigFactory = $deploymentConfigFactory;
        $this->moduleList = $moduleList;
        $this->moduleLoader = $moduleLoader;
        $this->directoryList = $directoryList;
        $this->adminAccountFactory = $adminAccountFactory;
        $this->log = $log;
        $this->random = $random;
        $this->connectionFactory = $connectionFactory;
        $this->shellRenderer = new CommandRenderer;
        $this->maintenanceMode = $maintenanceMode;
        $this->filesystem = $filesystem;
        $this->sampleData = $sampleData;
        $this->installInfo[self::INFO_MESSAGE] = [];
        $this->deploymentConfig = $deploymentConfig;
        $this->objectManagerProvider = $objectManagerProvider;
        $this->context = $context;
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
        $script[] = ['Enabling Maintenance Mode...', 'setMaintenanceMode', [1]];
        $script[] = ['Installing deployment configuration...', 'installDeploymentConfig', [$request]];
        if (!empty($request[self::CLEANUP_DB])) {
            $script[] = ['Cleaning up database...', 'cleanupDb', []];
        }
        $script[] = ['Installing database schema:', 'installSchema', []];
        $script[] = ['Installing user configuration...', 'installUserConfig', [$request]];
        $script[] = ['Installing data...', 'installDataFixtures', []];
        if (!empty($request[self::SALES_ORDER_INCREMENT_PREFIX])) {
            $script[] = [
                'Creating sales order increment prefix...',
                'installOrderIncrementPrefix',
                [$request[self::SALES_ORDER_INCREMENT_PREFIX]],
            ];
        }
        $script[] = ['Installing admin user...', 'installAdminUser', [$request]];
        $script[] = ['Enabling caches:', 'enableCaches', []];
        if (!empty($request[Installer::USE_SAMPLE_DATA]) && $this->sampleData->isDeployed()) {
            $script[] = ['Installing sample data:', 'installSampleData', [$request]];
        }
        $script[] = ['Disabling Maintenance Mode:', 'setMaintenanceMode', [0]];
        $script[] = ['Post installation file permissions check...', 'checkApplicationFilePermissions', []];

        $estimatedModules = $this->createModulesConfig($request);
        $total = count($script) + 3 * count(array_filter($estimatedModules->getData()));
        $this->progress = new Installer\Progress($total, 0);

        $this->log->log('Starting Magento installation:');

        while (list(, list($message, $method, $params)) = each($script)) {
            $this->log->log($message);
            call_user_func_array([$this, $method], $params);
            $this->logProgress();
        }

        $this->log->logSuccess('Magento installation complete.');

        if ($this->progress->getCurrent() != $this->progress->getTotal()) {
            throw new \LogicException('Installation progress did not finish properly.');
        }
    }

    /**
     * Creates modules deployment configuration segment
     *
     * @param \ArrayObject|array $request
     * @return DeploymentConfig
     * @throws \LogicException
     */
    private function createModulesConfig($request)
    {
        $all = array_keys($this->moduleLoader->load());
        $enable = $this->readListOfModules($all, $request, self::ENABLE_MODULES) ?: $all;
        $disable = $this->readListOfModules($all, $request, self::DISABLE_MODULES);
        $toEnable = array_diff($enable, $disable);
        if (empty($toEnable)) {
            throw new \LogicException('Unable to determine list of enabled modules.');
        }
        $result = [];
        foreach ($all as $module) {
            $key = array_search($module, $toEnable);
            $result[$module] = false !== $key;
        }
        return $this->deploymentConfigFactory->create($result);
    }

    /**
     * Creates backend deployment configuration segment
     *
     * @param \ArrayObject|array $data
     * @return \Magento\Framework\App\DeploymentConfig\SegmentInterface
     * @throws \InvalidArgumentException
     */
    private function createBackendConfig($data)
    {
        $key = DeploymentConfigMapper::KEY_BACKEND_FRONTNAME;
        if (empty($data[$key])) {
            throw new \InvalidArgumentException("Missing value for: '{$key}'");
        }
        return new BackendConfig([DeploymentConfigMapper::$paramMap[$key] => $data[$key]]);
    }

    /**
     * Creates encrypt deployment configuration segment
     * No new encryption key will be added if there is an existing deployment config file unless user provides one.
     * Old encryption keys will persist.
     * A new encryption key will be generated if there is no existing deployment config file.
     *
     * @param \ArrayObject|array $data
     * @return \Magento\Framework\App\DeploymentConfig\SegmentInterface
     */
    private function createEncryptConfig($data)
    {
        $key = '';
        if (isset($data[DeploymentConfigMapper::KEY_ENCRYPTION_KEY])) {
            $key = $data[DeploymentConfigMapper::KEY_ENCRYPTION_KEY];
        }
        // retrieve old encryption keys
        if ($this->deploymentConfig->isAvailable()) {
            $encryptInfo = $this->deploymentConfig->getSegment(EncryptConfig::CONFIG_KEY);
            $oldKeys = $encryptInfo[EncryptConfig::KEY_ENCRYPTION_KEY];
            $key = empty($key) ? $oldKeys : $oldKeys . "\n" . $key;
        } else if (empty($key)) {
            $key = md5($this->random->getRandomString(10));
        }
        $cryptConfigData =
            [DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_ENCRYPTION_KEY] => $key];

        // find the latest key to display
        $keys = explode("\n", $key);
        $this->installInfo[EncryptConfig::KEY_ENCRYPTION_KEY] = array_pop($keys);
        return new EncryptConfig($cryptConfigData);
    }

    /**
     * Creates db deployment configuration segment
     *
     * @param \ArrayObject|array $data
     * @return \Magento\Framework\App\DeploymentConfig\SegmentInterface
     * @throws \InvalidArgumentException
     */
    private function createDbConfig($data)
    {
        $connection = [];
        $required = [
            DeploymentConfigMapper::KEY_DB_HOST,
            DeploymentConfigMapper::KEY_DB_NAME,
            DeploymentConfigMapper::KEY_DB_USER,
        ];
        foreach ($required as $key) {
            if (!isset($data[$key])) {
                throw new \InvalidArgumentException("Missing value: {$key}");
            }
            $connection[DeploymentConfigMapper::$paramMap[$key]] = $data[$key];
        }
        $optional = [
            DeploymentConfigMapper::KEY_DB_INIT_STATEMENTS,
            DeploymentConfigMapper::KEY_DB_MODEL,
            DeploymentConfigMapper::KEY_DB_PASS,
        ];
        foreach ($optional as $key) {
            $connection[DeploymentConfigMapper::$paramMap[$key]] = isset($data[$key]) ? $data[$key] : null;
        }
        $prefixKey = DeploymentConfigMapper::KEY_DB_PREFIX;
        $config = [
            DeploymentConfigMapper::$paramMap[$prefixKey] => isset($data[$prefixKey]) ? $data[$prefixKey] : null,
            'connection' => ['default' => $connection],
        ];
        return new DbConfig($config);
    }

    /**
     * Creates session deployment configuration segment
     *
     * @param \ArrayObject|array $data
     * @return \Magento\Framework\App\DeploymentConfig\SegmentInterface
     */
    private function createSessionConfig($data)
    {
        $sessionConfigData = [
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_SESSION_SAVE] =>
                isset($data[DeploymentConfigMapper::KEY_SESSION_SAVE]) ?
                    $data[DeploymentConfigMapper::KEY_SESSION_SAVE] : null
        ];
        return new SessionConfig($sessionConfigData);
    }

    /**
     * Creates install deployment configuration segment
     *
     * @param \ArrayObject|array $data
     * @return \Magento\Framework\App\DeploymentConfig\SegmentInterface
     */
    private function createInstallConfig($data)
    {
        $installConfigData = [
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_DATE] =>
                $data[DeploymentConfigMapper::KEY_DATE]
        ];
        return new InstallConfig($installConfigData);
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
        $results = $this->filePermissions->getMissingWritableDirectoriesForInstallation();
        if ($results) {
            $errorMsg = "Missing writing permissions to the following directories: '" . implode("' '", $results) . "'";
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
        $data[InstallConfig::KEY_DATE] = date('r');

        $configs = [
            $this->createBackendConfig($data),
            $this->createDbConfig($data),
            $this->createEncryptConfig($data),
            $this->createInstallConfig($data),
            $this->createSessionConfig($data),
            new ResourceConfig(),
            $this->createModulesConfig($data),
        ];
        $this->deploymentConfigWriter->create($configs);
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
        $setup = $this->objectManagerProvider->get()->create(
            'Magento\Setup\Module\Setup',
            ['resource' => $this->context->getResources()]
        );
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
        $setup = $this->objectManagerProvider->get()->create('Magento\Setup\Module\DataSetup');
        $this->checkInstallationFilePermissions();
        $this->log->log('Data install/update:');
        $this->handleDBSchemaData($setup, 'data');
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

        $this->assertDeploymentConfigExists();
        $this->assertDbAccessible();

        $resource = new \Magento\Framework\Module\Resource($this->context);
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
                    $this->log->logInline("Installing $type.. ");
                    $installer->install($setup, $moduleContextList[$moduleName]);
                }
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
            $this->logProgress();
        }

        if ($type === 'schema') {
            $this->log->log('Schema post-updates:');
            foreach ($moduleNames as $moduleName) {
                $this->log->log("Module '{$moduleName}':");
                $modulePostUpdater = $this->getSchemaDataHandler($moduleName, 'schema-recurring');
                if ($modulePostUpdater) {
                    $this->log->logInline("Running recurring.. ");
                    $modulePostUpdater->install($setup, $moduleContextList[$moduleName]);
                }
                $this->logProgress();
            }
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
        $userConfig = new UserConfigurationDataMapper();
        $configData = $userConfig->getConfigData($data);
        if (count($configData) === 0) {
            return;
        }

        /** @var \Magento\Config\Model\Config\Factory $configFactory */
        $configFactory = $this->objectManagerProvider->get()->create('Magento\Config\Model\Config\Factory');
        foreach ($configData as $key => $val) {
            $configModel = $configFactory->create();
            $configModel->setDataByPath($key, $val);
            $configModel->save();
        }
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
        $setup = $this->objectManagerProvider->get()->create(
            'Magento\Setup\Module\Setup',
            ['resource' => $this->context->getResources()]
        );
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
        $this->assertDeploymentConfigExists();
        $setup = $this->objectManagerProvider->get()->create(
            'Magento\Setup\Module\Setup',
            ['resource' => $this->context->getResources()]
        );
        $adminAccount = $this->adminAccountFactory->create($setup, (array)$data);
        $adminAccount->save();
    }

    /**
     * Updates modules in deployment configuration
     *
     * @return void
     */
    public function updateModulesSequence()
    {
        $this->assertDeploymentConfigExists();
        $this->log->log('File system cleanup:');
        $this->deleteDirContents(DirectoryList::GENERATION);
        $this->deleteDirContents(DirectoryList::CACHE);
        $this->log->log('Updating modules:');
        $allModules = array_keys($this->moduleLoader->load());
        $deploymentConfig = $this->deploymentConfigReader->load();
        $currentModules = isset($deploymentConfig['modules']) ? $deploymentConfig['modules'] : [] ;
        $result = [];
        foreach ($allModules as $module) {
            if (isset($currentModules[$module]) && !$currentModules[$module]) {
                $result[$module] = 0;
            } else {
                $result[$module] = 1;
            }
        }
        $segment = $this->deploymentConfigFactory->create($result);
        $this->deploymentConfigWriter->update($segment);
    }

    /**
     * Uninstall Magento application
     *
     * @return void
     */
    public function uninstall()
    {
        $this->log->log('Starting Magento uninstallation:');

        $this->cleanupDb();
        $this->log->log('File system cleanup:');
        $this->deleteDirContents(DirectoryList::VAR_DIR);
        $this->deleteDirContents(DirectoryList::STATIC_VIEW);
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
        $cacheManager = $this->objectManagerProvider->get()->create('Magento\Framework\App\Cache\Manager');
        $types = $cacheManager->getAvailableTypes();
        $enabledTypes = $cacheManager->setEnabled($types, true);
        $cacheManager->clean($enabledTypes);

        $this->log->log('Current status:');
        $this->log->log(print_r($cacheManager->getStatus(), true));
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
     * Checks Database Connection
     *
     * @param string $dbName
     * @param string $dbHost
     * @param string $dbUser
     * @param string $dbPass
     * @return boolean
     * @throws \Magento\Setup\Exception
     */
    public function checkDatabaseConnection($dbName, $dbHost, $dbUser, $dbPass = '')
    {
        $connection = $this->connectionFactory->create([
            'dbname' => $dbName,
            'host' => $dbHost,
            'username' => $dbUser,
            'password' => $dbPass,
            'active' => true,
        ]);

        if (!$connection) {
            throw new \Magento\Setup\Exception('Database connection failure.');
        }

        $mysqlVersion = $connection->fetchOne('SELECT version()');
        if ($mysqlVersion) {
            if (preg_match('/^([0-9\.]+)/', $mysqlVersion, $matches)) {
                if (isset($matches[1]) && !empty($matches[1])) {
                    if (version_compare($matches[1], self::MYSQL_VERSION_REQUIRED) < 0) {
                        throw new \Magento\Setup\Exception(
                            'Sorry, but we support MySQL version '. self::MYSQL_VERSION_REQUIRED . ' or later.'
                        );
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check if database table prefix is valid
     *
     * @param string $prefix
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function checkDatabaseTablePrefix($prefix)
    {
        //The table prefix should contain only letters (a-z), numbers (0-9) or underscores (_);
        // the first character should be a letter.
        if ($prefix !== '' && !preg_match('/^([a-zA-Z])([[:alnum:]_]+)$/', $prefix)) {
            throw new \InvalidArgumentException('Please correct the table prefix format.');
        }

        return true;
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
        // stops cleanup if app/etc/config.php does not exist
        if ($this->deploymentConfig->isAvailable()) {
            $dbConfig = new DbConfig($this->deploymentConfig->getSegment(DbConfig::CONFIG_KEY));
            $config = $dbConfig->getConnection(\Magento\Framework\App\Resource\Config::DEFAULT_SETUP_CONNECTION);
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
                $dbName = $connection->quoteIdentifier($config['dbname']);
                $this->log->log("Recreating database {$dbName}");
                $connection->query("DROP DATABASE IF EXISTS {$dbName}");
                $connection->query("CREATE DATABASE IF NOT EXISTS {$dbName}");
                return;
            }
        }
        $this->log->log('No database connection defined - skipping database cleanup');
    }

    /**
     * Removes contents of a directory
     *
     * @param string $type
     * @return void
     */
    private function deleteDirContents($type)
    {
        $dir = $this->filesystem->getDirectoryWrite($type);
        $dirPath = $dir->getAbsolutePath();
        if (!$dir->isExist()) {
            $this->log->log("The directory '{$dirPath}' doesn't exist - skipping cleanup");
            return;
        }
        foreach ($dir->read() as $path) {
            if (preg_match('/^\./', $path)) {
                continue;
            }
            $this->log->log("{$dirPath}{$path}");
            try {
                $dir->delete($path);
            } catch (FilesystemException $e) {
                $this->log->log($e->getMessage());
            }
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
        $file = 'config.php';
        $absolutePath = $configDir->getAbsolutePath($file);
        if (!$configDir->isFile($file)) {
            $this->log->log("The file '{$absolutePath}' doesn't exist - skipping cleanup");
            return;
        }
        try {
            $this->log->log($absolutePath);
            $configDir->delete($file);
        } catch (FilesystemException $e) {
            $this->log->log($e->getMessage());
        }
    }

    /**
     * Validates that deployment configuration exists
     *
     * @throws \Magento\Setup\Exception
     * @return void
     */
    private function assertDeploymentConfigExists()
    {
        if (!$this->deploymentConfig->isAvailable()) {
            throw new \Magento\Setup\Exception("Can't run this operation: deployment configuration is absent.");
        }
    }

    /**
     * Validates that MySQL is accessible and MySQL version is supported
     *
     * @return void
     */
    private function assertDbAccessible()
    {
        $segment = $this->deploymentConfig->getSegment(DbConfig::CONFIG_KEY);
        $dbConfig = new DbConfig($segment);
        $config = $dbConfig->getConnection(\Magento\Framework\App\Resource\Config::DEFAULT_SETUP_CONNECTION);
        $this->checkDatabaseConnection(
            $config[DbConfig::KEY_NAME],
            $config[DbConfig::KEY_HOST],
            $config[DbConfig::KEY_USER],
            $config[DbConfig::KEY_PASS]
        );
        if (isset($config[DbConfig::KEY_PREFIX])) {
            $this->checkDatabaseTablePrefix($config[DbConfig::KEY_PREFIX]);
        }
    }

    /**
     * Run installation process for Sample Data
     *
     * @param array $request
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Called by install() via callback.
     */
    private function installSampleData($request)
    {
        $userName = isset($request[AdminAccount::KEY_USERNAME]) ? $request[AdminAccount::KEY_USERNAME] : '';
        $this->sampleData->install($this->objectManagerProvider->get(), $this->log, $userName);
    }

    /**
     * Get handler for schema or data install/upgrade/backup/uninstall etc.
     *
     * @param string $moduleName
     * @param string $type
     * @return InstallSchemaInterface | UpgradeSchemaInterface | InstallDataInterface | UpgradeDataInterface | null
     * @throws \Magento\Setup\Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getSchemaDataHandler($moduleName, $type)
    {
        $className = str_replace('_', '\\', $moduleName) . '\Setup';
        switch ($type) {
            case 'schema-install':
                $className .= '\InstallSchema';
                if (class_exists($className)) {
                    if (false == is_subclass_of($className, self::SCHEMA_INSTALL)
                        && $className !== self::SCHEMA_INSTALL) {
                        throw  new \Magento\Setup\Exception($className . ' must implement \\' . self::SCHEMA_INSTALL);
                    } else {
                        return $this->objectManagerProvider->get()->create($className);
                    }
                }
                break;
            case 'schema-upgrade':
                $className .= '\UpgradeSchema';
                if (class_exists($className)) {
                    if (false == is_subclass_of($className, self::SCHEMA_UPGRADE)
                        && $className !== self::SCHEMA_UPGRADE
                    ) {
                        throw  new \Magento\Setup\Exception($className . ' must implement \\' . self::SCHEMA_UPGRADE);
                    } else {
                        return $this->objectManagerProvider->get()->create($className);
                    }
                }
                break;
            case 'schema-recurring':
                $className .= '\Recurring';
                if (class_exists($className)) {
                    if (false == is_subclass_of($className, self::SCHEMA_INSTALL)
                        && $className !== self::SCHEMA_INSTALL) {
                        throw  new \Magento\Setup\Exception($className . ' must implement \\' . self::SCHEMA_INSTALL);
                    } else {
                        return $this->objectManagerProvider->get()->create($className);
                    }
                }
                break;
            case 'data-install':
                $className .= '\InstallData';
                if (class_exists($className)) {
                    if (false == is_subclass_of($className, self::DATA_INSTALL)
                        && $className !== self::DATA_INSTALL
                    ) {
                        throw  new \Magento\Setup\Exception($className . ' must implement \\' . self::DATA_INSTALL);
                    } else {
                        return $this->objectManagerProvider->get()->create($className);
                    }
                }
                break;
            case 'data-upgrade':
                $className .= '\UpgradeData';
                if (class_exists($className)) {
                    if (false == is_subclass_of($className, self::DATA_UPGRADE)
                        && $className !== self::DATA_UPGRADE
                    ) {
                        throw  new \Magento\Setup\Exception($className . ' must implement \\' . self::DATA_UPGRADE);
                    } else {
                        return $this->objectManagerProvider->get()->create($className);
                    }
                }
                break;
            default:
                throw  new \Magento\Setup\Exception("$className does not exist");
        }

        return null;
    }

    /**
     * Generates list of ModuleContext
     *
     * @param \Magento\Framework\Module\Resource $resource
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
}
