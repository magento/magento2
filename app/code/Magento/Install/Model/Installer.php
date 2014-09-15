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
namespace Magento\Install\Model;

/**
 * Installer model
 */
class Installer extends \Magento\Framework\Object
{
    /**
     * Installer data model used to store data between installation steps
     *
     * @var \Magento\Framework\Object
     */
    protected $_dataModel;

    /**
     * DB updated model
     *
     * @var \Magento\Framework\Module\Updater
     */
    protected $_dbUpdater;

    /**
     * Application chache model
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * Application config model
     *
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Install\Model\Setup
     */
    protected $_installSetup;

    /**
     * Install installer pear
     *
     * @var \Magento\Install\Model\Installer\Pear
     */
    protected $_installerPear;

    /**
     * Install installer filesystem
     *
     * @var \Magento\Install\Model\Installer\Filesystem
     */
    protected $_filesystem;

    /**
     * Area list
     *
     * @var \Magento\Framework\App\AreaList
     */
    protected $_areaList;

    /**
     * Application
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Store Manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * User user
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userModelFactory;

    /**
     * Installer DB model
     *
     * @var \Magento\Install\Model\Installer\Db
     */
    protected $_installerDb;

    /**
     * Installer DB model
     *
     * @var \Magento\Install\Model\Installer\Config
     */
    protected $_installerConfig;

    /**
     * Install session
     *
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * Configuration arguments
     *
     * @var \Magento\Framework\App\Arguments
     */
    protected $_arguments;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\Module\DependencyManagerInterface
     */
    protected $dependencyManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Framework\Module\Updater $dbUpdater
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Install\Model\Setup $installSetup
     * @param \Magento\Framework\App\Arguments $arguments
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\User\Model\UserFactory $userModelFactory
     * @param Installer\Filesystem $filesystem
     * @param Installer\Pear $installerPear
     * @param Installer\Db $installerDb
     * @param Installer\Config $installerConfig
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Module\DependencyManagerInterface $dependencyManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Framework\Module\Updater $dbUpdater,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Install\Model\Setup $installSetup,
        \Magento\Framework\App\Arguments $arguments,
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\User\Model\UserFactory $userModelFactory,
        \Magento\Install\Model\Installer\Filesystem $filesystem,
        \Magento\Install\Model\Installer\Pear $installerPear,
        \Magento\Install\Model\Installer\Db $installerDb,
        \Magento\Install\Model\Installer\Config $installerConfig,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\DependencyManagerInterface $dependencyManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = array()
    ) {
        $this->_dbUpdater = $dbUpdater;
        $this->_config = $config;
        $this->_cache = $cache;
        $this->_cacheState = $cacheState;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_installSetup = $installSetup;
        $this->_encryptor = $encryptor;
        $this->mathRandom = $mathRandom;
        $this->_arguments = $arguments;
        $this->_areaList = $areaList;
        $this->_appState = $appState;
        $this->_storeManager = $storeManager;
        $this->_userModelFactory = $userModelFactory;
        $this->_filesystem = $filesystem;
        $this->_installerPear = $installerPear;
        $this->_installerDb = $installerDb;
        $this->_installerConfig = $installerConfig;
        $this->_session = $session;
        $this->_resource = $resource;
        $this->moduleList = $moduleList;
        $this->dependencyManager = $dependencyManager;
        $this->messageManager = $messageManager;
        $this->_localeDate = $localeDate;
        $this->_localeResolver = $localeResolver;
        parent::__construct($data);
    }

    /**
     * Get data model
     *
     * @return \Magento\Framework\Object
     */
    public function getDataModel()
    {
        if (null === $this->_dataModel) {
            $this->setDataModel($this->_session);
        }
        return $this->_dataModel;
    }

    /**
     * Set data model to store data between installation steps
     *
     * @param \Magento\Framework\Object $model
     * @return $this
     */
    public function setDataModel($model)
    {
        $this->_dataModel = $model;
        return $this;
    }

    /**
     * Check packages (pear) downloads
     *
     * @return boolean
     */
    public function checkDownloads()
    {
        try {
            $this->_installerPear->checkDownloads();
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }
        $this->setDownloadCheckStatus($result);
        return $result;
    }

    /**
     * Check server settings
     *
     * @return bool
     */
    public function checkServer()
    {
        try {
            $this->checkExtensionsLoaded();
            $this->_filesystem->install();
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }
        $this->setData('server_check_status', $result);
        return $result;
    }

    /**
     * Retrieve server checking result status
     *
     * @return bool
     */
    public function getServerCheckStatus()
    {
        $status = $this->getData('server_check_status');
        if (is_null($status)) {
            $status = $this->checkServer();
        }
        return $status;
    }

    /**
     * Check all necessary extensions are loaded and available
     *
     * @return void
     * @throws \Exception
     */
    protected function checkExtensionsLoaded()
    {
        try {
            foreach ($this->moduleList->getModules() as $moduleData) {
                $this->dependencyManager->checkModuleDependencies($moduleData);
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * Installation config data
     *
     * @param   array $data
     * @return  $this
     */
    public function installConfig($data)
    {
        $data['db_active'] = true;

        $data = $this->_installerDb->checkDbConnectionData($data);

        $data = $this->_installerConfig->install($data);
        $this->getDataModel()->setConfigData($data);

        $this->_arguments->reload();
        $this->_resource->setTablePrefix($data['db_prefix']);

        $this->_config->reinit();

        return $this;
    }

    /**
     * Database installation
     *
     * @return $this
     */
    public function installDb()
    {
        $this->_dbUpdater->updateScheme();
        $data = $this->getDataModel()->getConfigData();

        /**
         * Saving host information into DB
         */
        if (!empty($data['use_rewrites'])) {
            $this->_installSetup->setConfigData(\Magento\Store\Model\Store::XML_PATH_USE_REWRITES, 1);
        }

        if (!empty($data['enable_charts'])) {
            $this->_installSetup->setConfigData(\Magento\Backend\Block\Dashboard::XML_PATH_ENABLE_CHARTS, 1);
        } else {
            $this->_installSetup->setConfigData(\Magento\Backend\Block\Dashboard::XML_PATH_ENABLE_CHARTS, 0);
        }

        if (!empty($data['admin_no_form_key'])) {
            $this->_installSetup->setConfigData('admin/security/use_form_key', 0);
        }

        $unsecureBaseUrl = $this->_storeManager->getStore()->getBaseUrl('web');
        if (!empty($data['unsecure_base_url'])) {
            $unsecureBaseUrl = $data['unsecure_base_url'];
            $this->_installSetup->setConfigData(
                \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL,
                $unsecureBaseUrl
            );
        }

        if (!empty($data['use_secure'])) {
            $this->_installSetup->setConfigData(\Magento\Store\Model\Store::XML_PATH_SECURE_IN_FRONTEND, 1);
            $this->_installSetup->setConfigData(
                \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL,
                $data['secure_base_url']
            );
            if (!empty($data['use_secure_admin'])) {
                $this->_installSetup->setConfigData(\Magento\Store\Model\Store::XML_PATH_SECURE_IN_ADMINHTML, 1);
            }
        } elseif (!empty($data['unsecure_base_url'])) {
            $this->_installSetup->setConfigData(\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, $unsecureBaseUrl);
        }

        /**
         * Saving locale information into DB
         */
        $locale = $this->getDataModel()->getLocaleData();
        if (!empty($locale['locale'])) {
            $this->_installSetup->setConfigData($this->_localeResolver->getDefaultLocalePath(), $locale['locale']);
        }
        if (!empty($locale['timezone'])) {
            $this->_installSetup->setConfigData($this->_localeDate->getDefaultTimezonePath(), $locale['timezone']);
        }
        if (!empty($locale['currency'])) {
            $this->_installSetup->setConfigData(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                $locale['currency']
            );
            $this->_installSetup->setConfigData(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
                $locale['currency']
            );
            $this->_installSetup->setConfigData(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
                $locale['currency']
            );
        }

        if (!empty($data['order_increment_prefix'])) {
            $this->_setOrderIncrementPrefix($this->_installSetup, $data['order_increment_prefix']);
        }

        return $this;
    }

    /**
     * Set order number prefix
     *
     * @param \Magento\Framework\Module\Setup $setupModel
     * @param string $orderIncrementPrefix
     * @return void
     */
    protected function _setOrderIncrementPrefix(\Magento\Framework\Module\Setup $setupModel, $orderIncrementPrefix)
    {
        $select = $setupModel->getConnection()->select()->from(
            $setupModel->getTable('eav_entity_type'),
            'entity_type_id'
        )->where(
            'entity_type_code=?',
            'order'
        );
        $data = array(
            'entity_type_id' => $setupModel->getConnection()->fetchOne($select),
            'store_id' => '1',
            'increment_prefix' => $orderIncrementPrefix
        );
        $setupModel->getConnection()->insert($setupModel->getTable('eav_entity_store'), $data);
    }

    /**
     * Create an admin user
     *
     * @param array $data
     * @return void
     */
    public function createAdministrator($data)
    {
        // \Magento\User\Model\User belongs to adminhtml area
        $this->_areaList
            ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
            ->load(\Magento\Framework\App\AreaInterface::PART_CONFIG);

        /** @var $user \Magento\User\Model\User */
        $user = $this->_userModelFactory->create();
        $user->loadByUsername($data['username']);
        // setForceNewPassword(true) - run-time flag to force saving of the entered password
        $user->addData($data)->setForceNewPassword(true)->setRoleId(1)->save();
        $this->_refreshConfig();
    }

    /**
     * Install encryption key into the application, generate and return a random one, if no value is specified
     *
     * @param string $key
     * @return $this
     */
    public function installEncryptionKey($key)
    {
        $this->_encryptor->validateKey($key);
        $this->_installerConfig->replaceTmpEncryptKey($key);
        $this->_refreshConfig();
        return $this;
    }

    /**
     * Return a validated encryption key, generating a random one, if no value was initially provided
     *
     * @param string|null $key
     * @return string
     */
    public function getValidEncryptionKey($key = null)
    {
        if (!$key) {
            $key = md5($this->mathRandom->getRandomString(10));
        }
        $this->_encryptor->validateKey($key);
        return $key;
    }

    /**
     * @return $this
     */
    public function finish()
    {
        $this->_setAppInstalled();
        $this->_refreshConfig();

        /* Enable all cache types */
        foreach (array_keys($this->_cacheTypeList->getTypes()) as $cacheTypeCode) {
            $this->_cacheState->setEnabled($cacheTypeCode, true);
        }
        $this->_cacheState->persist();
        return $this;
    }

    /**
     * Store install date and set application into installed state
     *
     * @return void
     */
    protected function _setAppInstalled()
    {
        $dateTime = date('r');
        $this->_installerConfig->replaceTmpInstallDate($dateTime);
        $this->_appState->setInstallDate($dateTime);
    }

    /**
     * Ensure changes in the configuration, if any, take effect
     *
     * @return void
     */
    protected function _refreshConfig()
    {
        $this->_cache->clean();
        $this->_config->reinit();
    }
}
