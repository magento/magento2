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

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Encapsulates application installation, initialization and uninstall
 *
 * @todo Implement MAGETWO-1689: Standard Installation Method for Integration Tests
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
     * @var \Magento\Framework\Simplexml\Element
     */
    protected $_localXml;

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
    protected $_installDir;

    /**
     * Installation destination directory with configuration files
     *
     * @var string
     */
    protected $_installEtcDir;

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
     * @var \Magento\TestFramework\ObjectManagerFactory
     */
    protected $_factory;

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Db\AbstractDb $dbInstance
     * @param string $installDir
     * @param \Magento\Framework\Simplexml\Element $localXml
     * @param $globalConfigDir
     * @param array $moduleEtcFiles
     * @param string $appMode
     */
    public function __construct(
        \Magento\TestFramework\Db\AbstractDb $dbInstance,
        $installDir,
        \Magento\Framework\Simplexml\Element $localXml,
        $globalConfigDir,
        array $moduleEtcFiles,
        $appMode
    ) {
        $this->_db = $dbInstance;
        $this->_localXml = $localXml;
        $this->_globalConfigDir = realpath($globalConfigDir);
        $this->_moduleEtcFiles = $moduleEtcFiles;
        $this->_appMode = $appMode;

        $this->_installDir = $installDir;
        $this->_installEtcDir = "{$installDir}/etc";

        $generationDir = "{$installDir}/generation";
        $this->_initParams = array(
            Filesystem::PARAM_APP_DIRS => array(
                Filesystem::CONFIG_DIR => array('path' => $this->_installEtcDir),
                Filesystem::VAR_DIR => array('path' => $installDir),
                Filesystem::MEDIA_DIR => array('path' => "{$installDir}/media"),
                Filesystem::STATIC_VIEW_DIR => array('path' => "{$installDir}/pub_static"),
                Filesystem::GENERATION_DIR => array('path' => $generationDir),
                Filesystem::CACHE_DIR => array('path' => $installDir . '/cache'),
                Filesystem::LOG_DIR => array('path' => $installDir . '/log'),
                Filesystem::THEMES_DIR => array('path' => BP . '/app/design'),
            ),
            \Magento\Framework\App\State::PARAM_MODE => $appMode
        );
        $this->_factory = new \Magento\TestFramework\ObjectManagerFactory();
    }

    /**
     * Retrieve the database adapter instance
     *
     * @return \Magento\TestFramework\Db\AbstractDb
     */
    public function getDbInstance()
    {
        return $this->_db;
    }

    /**
     * Get directory path with application instance custom data (cache, temporary directory, etc...)
     */
    public function getInstallDir()
    {
        return $this->_installDir;
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
        return is_file($this->_installEtcDir . '/local.xml');
    }

    /**
     * Initialize application
     *
     * @param array $overriddenParams
     */
    public function initialize($overriddenParams = array())
    {
        $overriddenParams['base_dir'] = BP;
        $overriddenParams[\Magento\Framework\App\State::PARAM_MODE] = $this->_appMode;
        $overriddenParams = $this->_customizeParams($overriddenParams);

        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Helper\Bootstrap::getObjectManager();
        if (!$objectManager) {
            $objectManager = $this->_factory->create(BP, $overriddenParams);
        } else {
            $objectManager = $this->_factory->restore($objectManager, BP, $overriddenParams);
        }

        $directories = isset(
            $overriddenParams[Filesystem::PARAM_APP_DIRS]
        ) ? $overriddenParams[Filesystem::PARAM_APP_DIRS] : array();
        $directoryList = new \Magento\TestFramework\App\Filesystem\DirectoryList(BP, $directories);

        $objectManager->addSharedInstance($directoryList, 'Magento\Framework\App\Filesystem\DirectoryList');
        $objectManager->addSharedInstance($directoryList, 'Magento\Framework\Filesystem\DirectoryList');
        $objectManager->removeSharedInstance('Magento\Framework\App\Filesystem');
        $objectManager->removeSharedInstance('Magento\Framework\App\Filesystem\DirectoryList\Verification');

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

        /** @var \Magento\Framework\App\Filesystem\DirectoryList\Verification $verification */
        $verification = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList\Verification');
        $verification->createAndVerifyDirectories();

        $directoryList = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $directoryListConfig = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList\Configuration');
        $directoryListConfig->configure($directoryList);

        $directories = isset(
            $overriddenParams[\Magento\Framework\App\Filesystem::PARAM_APP_DIRS]
        ) ? $overriddenParams[\Magento\Framework\App\Filesystem::PARAM_APP_DIRS] : array();
        foreach ($directories as $code => $configOverrides) {
            $config = array_merge($directoryList->getConfig($code), $configOverrides);
            $directoryList->addDirectory($code, $config);
        }
    }

    /**
     * Reset and initialize again an already installed application
     *
     * @param array $overriddenParams
     */
    public function reinitialize(array $overriddenParams = array())
    {
        $this->_resetApp();
        $this->initialize($overriddenParams);
    }

    /**
     * Run application normally, but with encapsulated initialization options
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
     */
    public function cleanup()
    {
        $this->_db->cleanup();
        $this->_cleanupFilesystem();
    }

    /**
     * Install an application
     *
     * @param string $adminUserName
     * @param string $adminPassword
     * @param string $adminRoleName
     * @throws \Magento\Framework\Exception
     */
    public function install($adminUserName, $adminPassword, $adminRoleName)
    {
        $this->_ensureDirExists($this->_installDir);
        $this->_ensureDirExists($this->_installEtcDir);
        $this->_ensureDirExists($this->_installDir . '/media');
        $this->_ensureDirExists($this->_installDir . '/static');

        // Copy configuration files
        $globalConfigFiles = glob($this->_globalConfigDir . '/{*,*/*}.xml', GLOB_BRACE);
        foreach ($globalConfigFiles as $file) {
            $targetFile = $this->_installEtcDir . str_replace($this->_globalConfigDir, '', $file);
            $this->_ensureDirExists(dirname($targetFile));
            copy($file, $targetFile);
        }

        foreach ($this->_moduleEtcFiles as $file) {
            $targetModulesDir = $this->_installEtcDir . '/modules';
            $this->_ensureDirExists($targetModulesDir);
            copy($file, $targetModulesDir . '/' . basename($file));
        }

        /* Make sure that local.xml does not contain an invalid installation date */
        $installDate = (string)$this->_localXml->install->date;
        if ($installDate && strtotime($installDate)) {
            throw new \Magento\Framework\Exception('Local configuration must contain an invalid installation date.');
        }

        /* Replace local.xml */
        $targetLocalXml = $this->_installEtcDir . '/local.xml';
        $this->_localXml->asNiceXml($targetLocalXml);

        /* Initialize an application in non-installed mode */
        $this->initialize();

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
            ->getArea('install')->load(\Magento\Framework\App\Area::PART_CONFIG);

        /* Run all install and data-install scripts */
        /** @var $updater \Magento\Framework\Module\Updater */
        $updater = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Module\Updater');
        $updater->updateScheme();
        $updater->updateData();

        /* Enable configuration cache by default in order to improve tests performance */
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Cache\StateInterface'
        );
        $cacheState->setEnabled(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER, true);
        $cacheState->setEnabled(\Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER, true);
        $cacheState->setEnabled(\Magento\Framework\App\Cache\Type\Translate::TYPE_IDENTIFIER, true);
        $cacheState->setEnabled(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER, true);
        $cacheState->persist();

        /* Fill installation date in local.xml to indicate that application is installed */
        $localXml = file_get_contents($targetLocalXml);
        $localXml = str_replace($installDate, date('r'), $localXml, $replacementCount);
        if ($replacementCount != 1) {
            throw new \Magento\Framework\Exception(
                "Unable to replace installation date properly in '{$targetLocalXml}' file."
            );
        }
        file_put_contents($targetLocalXml, $localXml, LOCK_EX);

        /* Add predefined admin user to the system */
        $this->_createAdminUser($adminUserName, $adminPassword, $adminRoleName);

        /* Switch an application to installed mode */
        $this->initialize();
        //hot fix for \Magento\Catalog\Model\Product\Attribute\Backend\SkuTest::testGenerateUniqueLongSku
        /** @var $appState \Magento\Framework\App\State */
        $appState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State');
        $appState->setInstallDate(date('r', strtotime('now')));
    }

    /**
     * Sub-routine for merging custom parameters with the ones defined in object state
     *
     * @param array $params
     * @return array
     */
    private function _customizeParams($params)
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
     * @throws \Magento\Framework\Exception
     * @param string $dir
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
     * Remove temporary files and directories from the filesystem
     */
    protected function _cleanupFilesystem()
    {
        if (!is_dir($this->_installDir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->_installDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $path) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }
        rmdir($this->_installDir);
    }

    /**
     * Creates predefined admin user to be used by tests, where admin session is required
     *
     * @param string $adminUserName
     * @param string $adminPassword
     * @param string $adminRoleName
     */
    protected function _createAdminUser($adminUserName, $adminPassword, $adminRoleName)
    {
        /** @var $user \Magento\User\Model\User */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\User\Model\User');
        $user->setData(
            array(
                'firstname' => 'firstname',
                'lastname' => 'lastname',
                'email' => 'admin@example.com',
                'username' => $adminUserName,
                'password' => $adminPassword,
                'is_active' => 1
            )
        );
        $user->save();

        /** @var $roleAdmin \Magento\Authorization\Model\Role */
        $roleAdmin = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Authorization\Model\Role');
        $roleAdmin->load($adminRoleName, 'role_name');

        /** @var $roleUser \Magento\Authorization\Model\Role */
        $roleUser = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Authorization\Model\Role');
        $roleUser->setData(
            array(
                'parent_id' => $roleAdmin->getId(),
                'tree_level' => $roleAdmin->getTreeLevel() + 1,
                'role_type' => \Magento\Authorization\Model\Acl\Role\User::ROLE_TYPE,
                'user_id' => $user->getId(),
                'user_type' => UserContextInterface::USER_TYPE_ADMIN,
                'role_name' => $user->getFirstname()
            )
        );
        $roleUser->save();
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
     * @param $areaCode
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
}
