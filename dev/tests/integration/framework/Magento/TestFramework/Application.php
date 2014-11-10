<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework;

use Magento\Framework\Code\Generator\FileResolver;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

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
     * Module declaration *.xml configuration files
     *
     * @var array
     */
    protected $_moduleEtcFiles;

    /**
     * Installation destination directory
     *
     * @var string
     */
    protected $_tmpDir;

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
    protected $_initParams = array();

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
    protected $_primaryConfigData = array();

    /**
     * Object manager factory
     *
     * @var \Magento\TestFramework\ObjectManagerFactory
     */
    protected $_factory;

    /**
     * A factory method
     *
     * @param string $installConfigFile
     * @param string $globalConfigDir
     * @param array $moduleConfigFiles
     * @param string $appMode
     * @param string $tmpDir
     * @param \Magento\Framework\Shell $shell
     * @return Application
     */
    public static function getInstance(
        $installConfigFile,
        $globalConfigDir,
        array $moduleConfigFiles,
        $appMode,
        $tmpDir,
        \Magento\Framework\Shell $shell
    ) {
        if (!file_exists($installConfigFile)) {
            $installConfigFile = $installConfigFile . '.dist';
        }
        $sandboxUniqueId = md5(sha1_file($installConfigFile));
        $installDir = "{$tmpDir}/sandbox-{$sandboxUniqueId}";
        FileResolver::addIncludePath($installDir . '/var/generation/');
        return new \Magento\TestFramework\Application(
            $shell,
            $installDir,
            $installConfigFile,
            $globalConfigDir,
            $moduleConfigFiles,
            $appMode
        );
    }

    /**
     * Constructor
     *
     * @param \Magento\Framework\Shell $shell
     * @param string $tmpDir
     * @param array $installConfigFile
     * @param string $globalConfigDir
     * @param array $moduleEtcFiles
     * @param string $appMode
     */
    public function __construct(
        \Magento\Framework\Shell $shell,
        $tmpDir,
        $installConfigFile,
        $globalConfigDir,
        array $moduleEtcFiles,
        $appMode
    ) {
        $this->_shell = $shell;
        $this->installConfigFile = $installConfigFile;
        $this->_globalConfigDir = realpath($globalConfigDir);
        $this->_moduleEtcFiles = $moduleEtcFiles;
        $this->_appMode = $appMode;

        $this->_tmpDir = $tmpDir;

        $customDirs = $this->getCustomDirs();
        $dirList = new \Magento\Framework\App\Filesystem\DirectoryList(BP, $customDirs);

        $this->_initParams = array(
            \Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => $customDirs,
            \Magento\Framework\App\State::PARAM_MODE => $appMode
        );
        $driverPool = new \Magento\Framework\Filesystem\DriverPool;
        $this->_factory = new \Magento\TestFramework\ObjectManagerFactory($dirList, $driverPool);

        $this->_configDir = $dirList->getPath(DirectoryList::CONFIG);
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
                $localConfigFile = $this->getLocalConfig();
                $localConfig = simplexml_load_file($localConfigFile);
                $host = (string)$localConfig->connection->host;
                $user = (string)$localConfig->connection->username;
                $password = (string)$localConfig->connection->password;
                $dbName = (string)$localConfig->connection->dbName;
            } else {
                $installConfig = $this->getInstallConfig();
                $host = $installConfig['db_host'];
                $user = $installConfig['db_user'];
                $password = $installConfig['db_pass'];
                $dbName = $installConfig['db_name'];
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
        return $this->_configDir . '/local.xml';
    }

    /**
     * Get path to temporary directory
     *
     * @return string
     */
    public function getTempDir()
    {
        return $this->_tmpDir;
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
    public function initialize($overriddenParams = array())
    {
        $overriddenParams[\Magento\Framework\App\State::PARAM_MODE] = $this->_appMode;
        $overriddenParams = $this->_customizeParams($overriddenParams);
        $directories = isset($overriddenParams[\Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS])
            ? $overriddenParams[\Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]
            : array();
        $directoryList = new DirectoryList(BP, $directories);

        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Helper\Bootstrap::getObjectManager();
        if (!$objectManager) {
            $objectManager = $this->_factory->create($overriddenParams);
            $objectManager->addSharedInstance($directoryList, 'Magento\Framework\App\Filesystem\DirectoryList');
            $objectManager->addSharedInstance($directoryList, 'Magento\Framework\Filesystem\DirectoryList');
        } else {
            $objectManager = $this->_factory->restore($objectManager, $directoryList, $overriddenParams);
        }

        /** @var \Magento\TestFramework\App\Filesystem $filesystem */
        $filesystem = $objectManager->get('Magento\TestFramework\App\Filesystem');
        $objectManager->removeSharedInstance('Magento\Framework\Filesystem');
        $objectManager->addSharedInstance($filesystem, 'Magento\Framework\Filesystem');

        Helper\Bootstrap::setObjectManager($objectManager);

        $objectManager->configure(
            array(
                'preferences' => array(
                    'Magento\Framework\App\State' => 'Magento\TestFramework\App\State'
                )
            )
        );

        /** Register event observer of Integration Framework */
        /** @var \Magento\Framework\Event\Config\Data $eventConfigData */
        $eventConfigData = $objectManager->get('Magento\Framework\Event\Config\Data');
        $eventConfigData->merge(
            array(
                'core_app_init_current_store_after' => array(
                    'integration_tests' => array(
                        'instance' => 'Magento\TestFramework\Event\Magento',
                        'method' => 'initStoreAfter',
                        'name' => 'integration_tests'
                    )
                )
            )
        );

        $this->loadArea(\Magento\TestFramework\Application::DEFAULT_APP_AREA);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(
            $objectManager->get('Magento\Framework\ObjectManager\DynamicConfigInterface')->getConfiguration()
        );
        \Magento\Framework\Phrase::setRenderer($objectManager->get('Magento\Framework\Phrase\RendererInterface'));
    }

    /**
     * Reset and initialize again an already installed application
     *
     * @param array $overriddenParams
     * @return void
     */
    public function reinitialize(array $overriddenParams = array())
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
        $app = $objectManager->get('Magento\Framework\App\Http');
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
        /**
         * @see \Magento\Setup\Mvc\Bootstrap\InitParamListener::BOOTSTRAP_PARAM
         */
        $this->_shell->execute(
            'php -f %s uninstall --magento_init_params=%s',
            [BP . '/setup/index.php', $this->getInitParamsQuery()]
        );
    }

    /**
     * Install an application
     *
     * @return void
     * @throws \Magento\Framework\Exception
     */
    public function install()
    {
        $dirs = \Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS;
        $this->_ensureDirExists($this->_tmpDir);
        $this->_ensureDirExists($this->_configDir);
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
            'php -f %s install ' . implode(' ', array_keys($installParams)),
            array_merge([BP . '/setup/index.php'], array_values($installParams))
        );

        // enable only specified list of caches
        $cacheScript = BP . '/dev/shell/cache.php';
        $initParamsQuery = $this->getInitParamsQuery();
        $this->_shell->execute('php -f %s -- --set=0 --bootstrap=%s', [$cacheScript, $initParamsQuery]);
        $cacheTypes = [
            \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
            \Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER,
            \Magento\Framework\App\Cache\Type\Translate::TYPE_IDENTIFIER,
            \Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER,
        ];
        $this->_shell->execute(
            'php -f %s -- --set=1 --types=%s --bootstrap=%s',
            [$cacheScript, implode(',', $cacheTypes), $initParamsQuery]
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
        $globalConfigFiles = glob($this->_globalConfigDir . '/{di.xml,local.xml.template,*/*.xml}', GLOB_BRACE);
        foreach ($globalConfigFiles as $file) {
            $targetFile = $this->_configDir . str_replace($this->_globalConfigDir, '', $file);
            $this->_ensureDirExists(dirname($targetFile));
            copy($file, $targetFile);
        }

        foreach ($this->_moduleEtcFiles as $file) {
            $targetModulesDir = $this->_configDir . '/modules';
            $this->_ensureDirExists($targetModulesDir);
            copy($file, $targetModulesDir . '/' . basename($file));
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
        $params['magento_init_params'] = $this->getInitParamsQuery();
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
     * @throws \Magento\Framework\Exception
     */
    protected function _ensureDirExists($dir)
    {
        if (!file_exists($dir)) {
            $old = umask(0);
            mkdir($dir, 0777);
            umask($old);
        } elseif (!is_dir($dir)) {
            throw new \Magento\Framework\Exception("'$dir' is not a directory.");
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
        $scope = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Config\Scope');
        $scope->setCurrentScope($areaCode);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\App\ObjectManager\ConfigLoader'
            )->load(
                $areaCode
            )
        );
        $app = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList');
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
        $var = "{$this->_tmpDir}/var";
        $customDirs = array(
            DirectoryList::CONFIG => array($path => "{$this->_tmpDir}/etc"),
            DirectoryList::VAR_DIR => array($path => $var),
            DirectoryList::MEDIA => array($path => "{$this->_tmpDir}/media"),
            DirectoryList::STATIC_VIEW => array($path => "{$this->_tmpDir}/pub_static"),
            DirectoryList::GENERATION => array($path => "{$var}/generation"),
            DirectoryList::CACHE => array($path => "{$var}/cache"),
            DirectoryList::LOG => array($path => "{$var}/log"),
            DirectoryList::THEMES => array($path => BP . '/app/design'),
            DirectoryList::SESSION => array($path => "{$var}/session"),
            DirectoryList::TMP => array($path => "{$var}/tmp"),
            DirectoryList::UPLOAD => array($path => "{$var}/upload"),
        );
        return $customDirs;
    }
}
