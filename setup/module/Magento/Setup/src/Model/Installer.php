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
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\FilesystemException;
use Magento\Framework\Math\Random;
use Magento\Framework\Module\ModuleList\DeploymentConfig;
use Magento\Framework\Module\ModuleList\Loader as ModuleLoader;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRenderer;
use Magento\Setup\Module\ConnectionFactory;
use Magento\Setup\Module\SetupFactory;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\Store\Model\Store;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Installer contains the logic to install Magento application.
 *
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
     * Resource setup factory
     *
     * @var SetupFactory;
     */
    private $setupFactory;

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
     * Initialization parameters for Magento application bootstrap
     *
     * @var string
     */
    private $initParams;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var SampleData
     */
    private $sampleData;

    /**
     * Constructor
     *
     * @param FilePermissions $filePermissions
     * @param Writer $deploymentConfigWriter
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param SetupFactory $setupFactory
     * @param ModuleListInterface $moduleList
     * @param ModuleLoader $moduleLoader
     * @param DirectoryList $directoryList
     * @param AdminAccountFactory $adminAccountFactory
     * @param LoggerInterface $log
     * @param Random $random
     * @param ConnectionFactory $connectionFactory
     * @param MaintenanceMode $maintenanceMode
     * @param Filesystem $filesystem
     * @param ServiceLocatorInterface $serviceManager
     * @param SampleData $sampleData
     */
    public function __construct(
        FilePermissions $filePermissions,
        Writer $deploymentConfigWriter,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        SetupFactory $setupFactory,
        ModuleListInterface $moduleList,
        ModuleLoader $moduleLoader,
        DirectoryList $directoryList,
        AdminAccountFactory $adminAccountFactory,
        LoggerInterface $log,
        Random $random,
        ConnectionFactory $connectionFactory,
        MaintenanceMode $maintenanceMode,
        Filesystem $filesystem,
        ServiceLocatorInterface $serviceManager,
        SampleData $sampleData
    ) {
        $this->filePermissions = $filePermissions;
        $this->deploymentConfigWriter = $deploymentConfigWriter;
        $this->setupFactory = $setupFactory;
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
        $this->initParams = $serviceManager->get(InitParamListener::BOOTSTRAP_PARAM);
        $this->sampleData = $sampleData;
        $this->installInfo[self::INFO_MESSAGE] = array();
        $this->deploymentConfig = $deploymentConfig;
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
        $total = count($script) + count(array_filter($estimatedModules->getData()));
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
        return new DeploymentConfig($result);
    }

    /**
     * Creates backend deployment configuration segment
     *
     * @param \ArrayObject|array $data
     * @return \Magento\Framework\App\DeploymentConfig\SegmentInterface
     */
    private function createBackendConfig($data)
    {
        $backendConfigData = array(
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_BACKEND_FRONTNAME] =>
                $data[DeploymentConfigMapper::KEY_BACKEND_FRONTNAME]
        );
        return new BackendConfig($backendConfigData);
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
        $key = $data[DeploymentConfigMapper::KEY_ENCRYPTION_KEY];
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
     */
    private function createDbConfig($data)
    {
        $defaultConnection = [
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_DB_HOST] =>
                $data[DeploymentConfigMapper::KEY_DB_HOST],
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_DB_INIT_STATEMENTS] =>
                isset($data[DeploymentConfigMapper::KEY_DB_INIT_STATEMENTS]) ?
                    $data[DeploymentConfigMapper::KEY_DB_INIT_STATEMENTS] : null,
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_DB_MODEL] =>
                isset($data[DeploymentConfigMapper::KEY_DB_MODEL]) ? $data[DeploymentConfigMapper::KEY_DB_MODEL] : null,
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_DB_NAME] =>
                $data[DeploymentConfigMapper::KEY_DB_NAME],
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_DB_PASS] =>
                isset($data[DeploymentConfigMapper::KEY_DB_PASS]) ? $data[DeploymentConfigMapper::KEY_DB_PASS] : null,
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_DB_USER] =>
                $data[DeploymentConfigMapper::KEY_DB_USER],
        ];

        $dbConfigData = [
            DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_DB_PREFIX] =>
                isset($data[DeploymentConfigMapper::KEY_DB_PREFIX]) ?
                    $data[DeploymentConfigMapper::KEY_DB_PREFIX] : null,
            'connection' => [
                'default' => $defaultConnection,
            ],
        ];
        return new DbConfig($dbConfigData);
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
            $errorMsg = 'Missing writing permissions to the following directories: ';
            foreach ($results as $result) {
                $errorMsg .= '\'' . $result . '\' ';
            }
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
            $errorMsg = 'For security, remove write permissions from these directories: ';
            foreach ($results as $result) {
                $errorMsg .= '\'' . $result . '\' ';
            }
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
     * Installs DB schema
     *
     * @return void
     */
    public function installSchema()
    {
        $this->assertDeploymentConfigExists();
        $this->assertDbAccessible();

        $moduleNames = $this->moduleList->getNames();

        $this->log->log('Schema creation/updates:');
        foreach ($moduleNames as $moduleName) {
            $this->log->log("Module '{$moduleName}':");
            $setup = $this->setupFactory->createSetupModule($this->log, $moduleName);
            $setup->applyUpdates();
            $this->logProgress();
        }

        $this->log->log('Schema post-updates:');
        foreach ($moduleNames as $moduleName) {
            $this->log->log("Module '{$moduleName}':");
            $setup = $this->setupFactory->createSetupModule($this->log, $moduleName);
            $setup->applyRecurringUpdates();
        }
    }

    /**
     * Installs data fixtures
     *
     * @return void
     * @throws \Exception
     */
    public function installDataFixtures()
    {
        $this->checkInstallationFilePermissions();
        $this->assertDeploymentConfigExists();
        $this->assertDbAccessible();

        /** @var \Magento\Framework\Module\Updater $updater */
        $updater = $this->getObjectManager()->create('Magento\Framework\Module\Updater');
        $updater->updateData();
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

        /** @var \Magento\Backend\Model\Config\Factory $configFactory */
        $configFactory = $this->getObjectManager()->create('Magento\Backend\Model\Config\Factory');
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
     */
    private function installOrderIncrementPrefix($orderIncrementPrefix)
    {
        $setup = $this->setupFactory->createSetup($this->log);
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
        $setup = $this->setupFactory->createSetup($this->log);
        $adminAccount = $this->adminAccountFactory->create($setup, (array)$data);
        $adminAccount->save();
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
     */
    private function enableCaches()
    {
        /** @var \Magento\Framework\App\Cache\Manager $cacheManager */
        $cacheManager = $this->getObjectManager()->create('Magento\Framework\App\Cache\Manager');
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
    private function cleanupDb()
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
     * Get object manager for Magento application
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    private function getObjectManager()
    {
        if (null === $this->objectManager) {
            $this->assertDeploymentConfigExists();
            $factory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $this->initParams);
            $this->objectManager = $factory->create($this->initParams);
        }
        return $this->objectManager;
    }

    /**
     * Validates that deployment configuration exists
     *
     * @throws \Magento\Setup\Exception
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
        $dbConfig = new DbConfig($this->deploymentConfig->getSegment(DbConfig::CONFIG_KEY));
        $config = $dbConfig->getConnection(\Magento\Framework\App\Resource\Config::DEFAULT_SETUP_CONNECTION);
        $this->checkDatabaseConnection(
            $config[DbConfig::KEY_NAME],
            $config[DbConfig::KEY_HOST],
            $config[DbConfig::KEY_USER],
            $config[DbConfig::KEY_PASS]
        );
    }

    /**
     * Run installation process for Sample Data
     *
     * @param array $request
     * @return void
     */
    private function installSampleData($request)
    {
        $userName = isset($request[AdminAccount::KEY_USERNAME]) ? $request[AdminAccount::KEY_USERNAME] : '';
        $this->sampleData->install($this->getObjectManager(), $this->log, $userName);
    }
}
