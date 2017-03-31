<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework;

use Magento\Framework\Autoload\AutoloaderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Filesystem\Glob;

/**
 * Encapsulates application installation, initialization and uninstall
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Application
{
    /**
     * Default application area
     */
    const DEFAULT_APP_AREA = 'global';

    /**
     * DB vendor adapter instance
     *
     * @var \Magento\TestFramework\Db\AbstractDb
     */
    protected $_db;

    /**
     * Shell command executor
     *
     * @var \Magento\Framework\Shell
     */
    protected $_shell;

    /**
     * Configuration file that contains installation parameters
     *
     * @var string
     */
    private $installConfigFile;

    /**
     * The loaded installation parameters
     *
     * @var array
     */
    protected $installConfig;

    /**
     * Application *.xml configuration files
     *
     * @var array
     */
    protected $_globalConfigDir;

    /**
     * Installation destination directory
     *
     * @var string
     */
    protected $installDir;

    /**
     * Installation destination directory with configuration files
     *
     * @var string
     */
    protected $_configDir;

    /**
     * Application initialization parameters
     *
     * @var array
     */
    protected $_initParams = [];

    /**
     * Mode to run application
     *
     * @var string
     */
    protected $_appMode;

    /**
     * Application area
     *
     * @var null
     */
    protected $_appArea = null;

    /**
     * Primary DI Config
     *
     * @var array
     */
    protected $_primaryConfigData = [];

    /**
     * Object manager factory
     *
     * @var \Magento\TestFramework\ObjectManagerFactory
     */
    protected $_factory;

    /**
     * Directory List
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $dirList;

    /**
     * Config file for integration tests
     *
     * @var string
     */
    private $globalConfigFile;

    /**
     * Defines whether load test extension attributes or not
     *
     * @var bool
     */
    private $loadTestExtensionAttributes;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Shell $shell
     * @param string $installDir
     * @param array $installConfigFile
     * @param string $globalConfigFile
     * @param string $globalConfigDir
     * @param string $appMode
     * @param AutoloaderInterface $autoloadWrapper
     * @param bool|null $loadTestExtensionAttributes
     */
    public function __construct(
        \Magento\Framework\Shell $shell,
        $installDir,
        $installConfigFile,
        $globalConfigFile,
        $globalConfigDir,
        $appMode,
        AutoloaderInterface $autoloadWrapper,
        $loadTestExtensionAttributes = false
    ) {
        if (getcwd() != BP . '/dev/tests/integration') {
            chdir(BP . '/dev/tests/integration');
        }
        $this->_shell = $shell;
        $this->installConfigFile = $installConfigFile;
        $this->_globalConfigDir = realpath($globalConfigDir);
        $this->_appMode = $appMode;
        $this->installDir = $installDir;
        $this->loadTestExtensionAttributes = $loadTestExtensionAttributes;

        $customDirs = $this->getCustomDirs();
        $this->dirList = new \Magento\Framework\App\Filesystem\DirectoryList(BP, $customDirs);
        \Magento\Framework\Autoload\Populator::populateMappings(
            $autoloadWrapper,
            $this->dirList
        );
        $this->_initParams = [
            \Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => $customDirs,
            \Magento\Framework\App\State::PARAM_MODE => $appMode
        ];
        $driverPool = new \Magento\Framework\Filesystem\DriverPool;
        $configFilePool = new \Magento\Framework\Config\File\ConfigFilePool;
        $this->_factory = new \Magento\TestFramework\ObjectManagerFactory($this->dirList, $driverPool, $configFilePool);

        $this->_configDir = $this->dirList->getPath(DirectoryList::CONFIG);
        $this->globalConfigFile = $globalConfigFile;
    }

    /**
     * Retrieve the database adapter instance
     *
     * @return \Magento\TestFramework\Db\AbstractDb
     */
    public function getDbInstance()
    {
        if (null === $this->_db) {
            if ($this->isInstalled()) {
                $configPool = new \Magento\Framework\Config\File\ConfigFilePool();
                $driverPool = new \Magento\Framework\Filesystem\DriverPool();
                $reader = new Reader($this->dirList, $driverPool, $configPool);
                $deploymentConfig = new DeploymentConfig($reader, []);
                $host = $deploymentConfig->get(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                    '/' . ConfigOptionsListConstants::KEY_HOST
                );
                $user = $deploymentConfig->get(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                    '/' . ConfigOptionsListConstants::KEY_USER
                );
                $password = $deploymentConfig->get(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                    '/' . ConfigOptionsListConstants::KEY_PASSWORD
                );
                $dbName = $deploymentConfig->get(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                    '/' . ConfigOptionsListConstants::KEY_NAME
                );
            } else {
                $installConfig = $this->getInstallConfig();
                $host = $installConfig['db-host'];
                $user = $installConfig['db-user'];
                $password = $installConfig['db-password'];
                $dbName = $installConfig['db-name'];
            }
            $this->_db = new Db\Mysql(
                $host,
                $user,
                $password,
                $dbName,
                $this->getTempDir(),
                $this->_shell
            );
        }
        return $this->_db;
    }

    /**
     * Gets installation parameters
     *
     * @return array
     */
    protected function getInstallConfig()
    {
        if (null === $this->installConfig) {
            $this->installConfig = include $this->installConfigFile;
        }
        return $this->installConfig;
    }

    /**
     * Gets deployment configuration path
     *
     * @return string
     */
    private function getLocalConfig()
    {
        return $this->_configDir . '/config.php';
    }

    /**
     * Get path to temporary directory
     *
     * @return string
     */
    public function getTempDir()
    {
        return $this->installDir;
    }

    /**
     * Retrieve application initialization parameters
     *
     * @return array
     */
    public function getInitParams()
    {
        return $this->_initParams;
    }

    /**
     * Weather the application is installed or not
     *
     * @return bool
     */
    public function isInstalled()
    {
        return is_file($this->getLocalConfig());
    }

    /**
     * Initialize application
     *
     * @param array $overriddenParams
     * @return void
     */
    public function initialize($overriddenParams = [])
    {
        $overriddenParams[\Magento\Framework\App\State::PARAM_MODE] = $this->_appMode;
        $overriddenParams = $this->_customizeParams($overriddenParams);
        $directories = isset($overriddenParams[\Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS])
            ? $overriddenParams[\Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]
            : [];
        $directoryList = new DirectoryList(BP, $directories);
        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Helper\Bootstrap::getObjectManager();
        if (!$objectManager) {
            $objectManager = $this->_factory->create($overriddenParams);
            $objectManager->addSharedInstance($directoryList, \Magento\Framework\App\Filesystem\DirectoryList::class);
            $objectManager->addSharedInstance($directoryList, \Magento\Framework\Filesystem\DirectoryList::class);
        } else {
            $objectManager = $this->_factory->restore($objectManager, $directoryList, $overriddenParams);
        }
        /** @var \Magento\TestFramework\App\Filesystem $filesystem */
        $filesystem = $objectManager->get(\Magento\TestFramework\App\Filesystem::class);
        $objectManager->removeSharedInstance(\Magento\Framework\Filesystem::class);
        $objectManager->addSharedInstance($filesystem, \Magento\Framework\Filesystem::class);
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $objectManager->create(
            \Magento\TestFramework\ErrorLog\Logger::class,
            [
                'name' => 'integration-tests',
                'handlers' => [
                    'system' => $objectManager->create(
                        \Magento\Framework\Logger\Handler\System::class,
                        [
                            'exceptionHandler' => $objectManager->create(
                                \Magento\Framework\Logger\Handler\Exception::class,
                                ['filePath' => $this->installDir]
                            ),
                            'filePath' => $this->installDir
                        ]
                    ),
                    'debug'  => $objectManager->create(
                        \Magento\Framework\Logger\Handler\Debug::class,
                        ['filePath' => $this->installDir]
                    ),
                ]
            ]
        );
        $objectManager->removeSharedInstance(\Magento\Framework\Logger\Monolog::class);
        $objectManager->addSharedInstance($logger, \Magento\Framework\Logger\Monolog::class);
        $sequenceBuilder = $objectManager->get(\Magento\TestFramework\Db\Sequence\Builder::class);
        $objectManager->addSharedInstance($sequenceBuilder, \Magento\SalesSequence\Model\Builder::class);
        Helper\Bootstrap::setObjectManager($objectManager);
        $objectManagerConfiguration = [
            'preferences' => [
                \Magento\Framework\App\State::class => \Magento\TestFramework\App\State::class,
                \Magento\Framework\Mail\TransportInterface::class =>
                    \Magento\TestFramework\Mail\TransportInterfaceMock::class,
                \Magento\Framework\Mail\Template\TransportBuilder::class
                    => \Magento\TestFramework\Mail\Template\TransportBuilderMock::class,
            ]
        ];
        if ($this->loadTestExtensionAttributes) {
            $objectManagerConfiguration = array_merge(
                $objectManagerConfiguration,
                [
                    \Magento\Framework\Api\ExtensionAttribute\Config\Reader::class => [
                        'arguments' => [
                            'fileResolver' => [
                                'instance' => \Magento\TestFramework\Api\Config\Reader\FileResolver::class
                            ],
                        ],
                    ],
                ]
            );
        }
        $objectManager->configure($objectManagerConfiguration);
        /** Register event observer of Integration Framework */
        /** @var \Magento\Framework\Event\Config\Data $eventConfigData */
        $eventConfigData = $objectManager->get(\Magento\Framework\Event\Config\Data::class);
        $eventConfigData->merge(
            [
                'core_app_init_current_store_after' => [
                    'integration_tests' => [
                        'instance' => \Magento\TestFramework\Event\Magento::class,
                        'name' => 'integration_tests'
                    ]
                ]
            ]
        );
        $this->loadArea(\Magento\TestFramework\Application::DEFAULT_APP_AREA);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(
            $objectManager->get(\Magento\Framework\ObjectManager\DynamicConfigInterface::class)->getConfiguration()
        );
        \Magento\Framework\Phrase::setRenderer(
            $objectManager->get(\Magento\Framework\Phrase\Renderer\Placeholder::class)
        );
        /** @var \Magento\TestFramework\Db\Sequence $sequence */
        $sequence = $objectManager->get(\Magento\TestFramework\Db\Sequence::class);
        $sequence->generateSequences();
        $objectManager->create(\Magento\TestFramework\Config::class, ['configPath' => $this->globalConfigFile])
            ->rewriteAdditionalConfig();
    }

    /**
     * Reset and initialize again an already installed application
     *
     * @param array $overriddenParams
     * @return void
     */
    public function reinitialize(array $overriddenParams = [])
    {
        $this->_resetApp();
        $this->initialize($overriddenParams);
    }

    /**
     * Run application normally, but with encapsulated initialization options
     *
     * @return void
     */
    public function run()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\Http $app */
        $app = $objectManager->get(\Magento\Framework\App\Http::class);
        $response = $app->launch();
        $response->sendResponse();
    }

    /**
     * Cleanup both the database and the file system
     *
     * @return void
     */
    public function cleanup()
    {
        $this->_ensureDirExists($this->installDir);
        $this->_ensureDirExists($this->_configDir);

        $this->copyAppConfigFiles();
        /**
         * @see \Magento\Setup\Mvc\Bootstrap\InitParamListener::BOOTSTRAP_PARAM
         */
        $this->_shell->execute(
            PHP_BINARY . ' -f %s setup:uninstall -vvv -n --magento-init-params=%s',
            [BP . '/bin/magento', $this->getInitParamsQuery()]
        );
    }

    /**
     * Install an application
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function install()
    {
        $dirs = \Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS;
        $this->_ensureDirExists($this->installDir);
        $this->_ensureDirExists($this->_configDir);
        $this->_ensureDirExists($this->_initParams[$dirs][DirectoryList::PUB][DirectoryList::PATH]);
        $this->_ensureDirExists($this->_initParams[$dirs][DirectoryList::MEDIA][DirectoryList::PATH]);
        $this->_ensureDirExists($this->_initParams[$dirs][DirectoryList::STATIC_VIEW][DirectoryList::PATH]);
        $this->_ensureDirExists($this->_initParams[$dirs][DirectoryList::VAR_DIR][DirectoryList::PATH]);

        $this->copyAppConfigFiles();

        $installParams = $this->getInstallCliParams();

        // performance optimization: restore DB from last good dump to make installation on top of it (much faster)
        $db = $this->getDbInstance();
        if ($db->isDbDumpExists()) {
            $db->restoreFromDbDump();
        }

        // run install script
        $this->_shell->execute(
            PHP_BINARY . ' -f %s setup:install -vvv ' . implode(' ', array_keys($installParams)),
            array_merge([BP . '/bin/magento'], array_values($installParams))
        );

        // enable only specified list of caches
        $initParamsQuery = $this->getInitParamsQuery();
        $this->_shell->execute(
            PHP_BINARY . ' -f %s cache:disable -vvv --bootstrap=%s',
            [BP . '/bin/magento', $initParamsQuery]
        );
        $this->_shell->execute(
            PHP_BINARY . ' -f %s cache:enable -vvv %s %s %s %s --bootstrap=%s',
            [
                BP . '/bin/magento',
                \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
                \Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER,
                \Magento\Framework\App\Cache\Type\Translate::TYPE_IDENTIFIER,
                \Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER,
                $initParamsQuery,
            ]
        );

        // right after a clean installation, store DB dump for future reuse in tests or running the test suite again
        if (!$db->isDbDumpExists()) {
            $this->getDbInstance()->storeDbDump();
        }
    }

    /**
     * Copies configuration files from the main code base, so the installation could proceed in the tests directory
     *
     * @return void
     */
    private function copyAppConfigFiles()
    {
        $globalConfigFiles = Glob::glob(
            $this->_globalConfigDir . '/{di.xml,*/di.xml,vendor_path.php}',
            Glob::GLOB_BRACE
        );
        foreach ($globalConfigFiles as $file) {
            $targetFile = $this->_configDir . str_replace($this->_globalConfigDir, '', $file);
            $this->_ensureDirExists(dirname($targetFile));
            if ($file !== $targetFile) {
                copy($file, $targetFile);
            }
        }
    }

    /**
     * Gets a list of CLI params for installation
     *
     * @return array
     */
    private function getInstallCliParams()
    {
        $params = $this->getInstallConfig();
        /**
         * Literal value is used instead of constant, because autoloader is not integrated with Magento Setup app
         * @see \Magento\Setup\Mvc\Bootstrap\InitParamListener::BOOTSTRAP_PARAM
         */
        $params['magento-init-params'] = $this->getInitParamsQuery();
        $result = [];
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $result["--{$key}=%s"] = $value;
            }
        }
        return $result;
    }

    /**
     * Encodes init params into a query string
     *
     * @return string
     */
    private function getInitParamsQuery()
    {
        return urldecode(http_build_query($this->_initParams));
    }

    /**
     * Sub-routine for merging custom parameters with the ones defined in object state
     *
     * @param array $params
     * @return array
     */
    public function _customizeParams($params)
    {
        return array_replace_recursive($this->_initParams, $params);
    }

    /**
     * Reset application global state
     */
    protected function _resetApp()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->clearCache();
        \Magento\Framework\Data\Form::setElementRenderer(null);
        \Magento\Framework\Data\Form::setFieldsetRenderer(null);
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(null);
        $this->_appArea = null;
    }

    /**
     * Create a directory with write permissions or don't touch existing one
     *
     * @param string $dir
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _ensureDirExists($dir)
    {
        if (!file_exists($dir)) {
            $old = umask(0);
            mkdir($dir);
            umask($old);
        } elseif (!is_dir($dir)) {
            throw new \Magento\Framework\Exception\LocalizedException(__("'%1' is not a directory.", $dir));
        }
    }

    /**
     * Ge current application area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->_appArea;
    }

    /**
     * Load application area
     *
     * @param string $areaCode
     * @return void
     */
    public function loadArea($areaCode)
    {
        $this->_appArea = $areaCode;
        $scope = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Config\Scope::class
        );
        $scope->setCurrentScope($areaCode);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\App\ObjectManager\ConfigLoader::class
            )->load(
                $areaCode
            )
        );
        $app = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\AreaList::class);
        if ($areaCode == \Magento\TestFramework\Application::DEFAULT_APP_AREA) {
            $app->getArea($areaCode)->load(\Magento\Framework\App\Area::PART_CONFIG);
        } else {
            \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($areaCode);
        }
    }

    /**
     * Gets customized directory paths
     *
     * @return array
     */
    protected function getCustomDirs()
    {
        $path = DirectoryList::PATH;
        $var = "{$this->installDir}/var";
        $generated = "{$this->installDir}/generated";
        $customDirs = [
            DirectoryList::CONFIG => [$path => "{$this->installDir}/etc"],
            DirectoryList::VAR_DIR => [$path => $var],
            DirectoryList::MEDIA => [$path => "{$this->installDir}/pub/media"],
            DirectoryList::STATIC_VIEW => [$path => "{$this->installDir}/pub/static"],
            DirectoryList::TMP_MATERIALIZATION_DIR => [$path => "{$var}/view_preprocessed/pub/static"],
            DirectoryList::GENERATED_CODE => [$path => "{$generated}/code"],
            DirectoryList::CACHE => [$path => "{$var}/cache"],
            DirectoryList::LOG => [$path => "{$var}/log"],
            DirectoryList::SESSION => [$path => "{$var}/session"],
            DirectoryList::TMP => [$path => "{$var}/tmp"],
            DirectoryList::UPLOAD => [$path => "{$var}/upload"],
            DirectoryList::PUB => [$path => "{$this->installDir}/pub"],
        ];
        return $customDirs;
    }
}
