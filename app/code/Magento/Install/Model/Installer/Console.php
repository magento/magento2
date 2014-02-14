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
 * @category    Magento
 * @package     Magento_Install
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento application console installer
 */
namespace Magento\Install\Model\Installer;

class Console extends \Magento\Install\Model\Installer\AbstractInstaller
{
    /**#@+
     * Installation options for application initialization
     */
    const OPTION_URIS = 'install_option_uris';
    const OPTION_DIRS = 'install_option_dirs';
    /**#@- */

    /**
     * Available installation options
     *
     * @var array
     */
    protected $_installOptions = array(
        'license_agreement_accepted' => array('required' => 1),
        'locale'                     => array('required' => 1),
        'timezone'                   => array('required' => 1),
        'default_currency'           => array('required' => 1),
        'db_model'                   => array('required' => 0),
        'db_host'                    => array('required' => 1),
        'db_name'                    => array('required' => 1),
        'db_user'                    => array('required' => 1),
        'db_pass'                    => array('required' => 0),
        'db_prefix'                  => array('required' => 0),
        'url'                        => array('required' => 1),
        'skip_url_validation'        => array('required' => 0),
        'use_rewrites'               => array('required' => 1),
        'use_secure'                 => array('required' => 1),
        'secure_base_url'            => array('required' => 1),
        'use_secure_admin'           => array('required' => 1),
        'admin_lastname'             => array('required' => 1),
        'admin_firstname'            => array('required' => 1),
        'admin_email'                => array('required' => 1),
        'admin_username'             => array('required' => 1),
        'admin_password'             => array('required' => 1),
        'admin_no_form_key'          => array('required' => 0),
        'encryption_key'             => array('required' => 0),
        'session_save'               => array('required' => 0),
        'backend_frontname'          => array('required' => 0),
        'enable_charts'              => array('required' => 0),
        'order_increment_prefix'     => array('required' => 0),
        'cleanup_database'           => array('required' => 0),
    );

    /**
     * @var \Magento\App\Filesystem
     */
    protected $_filesystem;

    /**
     * Installer data model to store data between installations steps
     *
     * @var \Magento\Install\Model\Installer\Data|\Magento\Session\Generic
     */
    protected $_dataModel;

    /**
     * Resource config
     *
     * @var \Magento\App\Resource\Config
     */
    protected $_resourceConfig;

    /**
     * DB updater
     *
     * @var \Magento\Module\UpdaterInterface
     */
    protected $_dbUpdater;

    /**
     * Install installer data
     *
     * @var \Magento\Install\Model\Installer\Data
     */
    protected $_installerData = null;

    /**
     * Application State
     *
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * Locale model
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Magento Object Manager
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\Install\Model\Installer $installer
     * @param \Magento\App\Resource\Config $resourceConfig
     * @param \Magento\Module\UpdaterInterface $dbUpdater
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Install\Model\Installer\Data $installerData
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Install\Model\Installer $installer,
        \Magento\App\Resource\Config $resourceConfig,
        \Magento\Module\UpdaterInterface $dbUpdater,
        \Magento\App\Filesystem $filesystem,
        \Magento\Install\Model\Installer\Data $installerData,
        \Magento\App\State $appState,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\ObjectManager $objectManager
    ) {
        parent::__construct($installer);
        $this->_resourceConfig = $resourceConfig;
        $this->_dbUpdater = $dbUpdater;
        $this->_filesystem = $filesystem;
        $this->_installerData = $installerData;
        $this->_installer->setDataModel($this->_installerData);
        $this->_appState = $appState;
        $this->_locale = $locale;
        $this->_objectManager = $objectManager;
    }

    /**
     * Retrieve validated installation options
     *
     * @param array $options
     * @return array|false
     */
    protected function _getInstallOptions(array $options)
    {
        /**
         * Check required options
         */
        foreach ($this->_installOptions as $optionName => $optionInfo) {
            if (isset($optionInfo['required']) && $optionInfo['required'] && !isset($options[$optionName])) {
                $this->addError("ERROR: installation option '$optionName' is required.");
            }
        }

        if ($this->hasErrors()) {
            return false;
        }

        /**
         * Validate license agreement acceptance
         */
        if (!$this->_getFlagValue($options['license_agreement_accepted'])) {
            $this->addError(
                'ERROR: You have to accept Magento license agreement terms and conditions to continue installation.'
            );
            return false;
        }

        $result = array();
        foreach ($this->_installOptions as $optionName => $optionInfo) {
            $result[$optionName] = isset($options[$optionName]) ? $options[$optionName] : '';
        }

        return $result;
    }

    /**
     * Add error
     *
     * @param string $error
     * @return $this
     */
    public function addError($error)
    {
        $this->_installerData->addError($error);
        return $this;
    }

    /**
     * Check if there were any errors
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return (count($this->_installerData->getErrors()) > 0);
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_installerData->getErrors();
    }

    /**
     * Return TRUE for 'yes', 1, 'true' (case insensitive) or FALSE otherwise
     *
     * @param string $value
     * @return boolean
     */
    protected function _getFlagValue($value)
    {
        $res = (1 == $value) || preg_match('/^(yes|y|true)$/i', $value);
        return $res;
    }

    /**
     * Install Magento
     *
     * @param array $options
     * @return string|false
     */
    public function install(array $options)
    {
        try {
            $options = $this->_getInstallOptions($options);
            if (!$options) {
                return false;
            }

            /**
             * Check if already installed
             */
            if ($this->_appState->isInstalled()) {
                $this->addError('ERROR: Magento is already installed.');
                return false;
            }

            /**
             * Skip URL validation, if set
             */
            $this->_installerData->setSkipUrlValidation($options['skip_url_validation']);
            $this->_installerData->setSkipBaseUrlValidation($options['skip_url_validation']);

            /**
             * Locale settings
             */
            $this->_installerData->setLocaleData(array(
                'locale'            => $options['locale'],
                'timezone'          => $options['timezone'],
                'currency'          => $options['default_currency'],
            ));

            /**
             * Database and web config
             */
            $this->_installerData->setConfigData(array(
                'db_model'               => $options['db_model'],
                'db_host'                => $options['db_host'],
                'db_name'                => $options['db_name'],
                'db_user'                => $options['db_user'],
                'db_pass'                => $options['db_pass'],
                'db_prefix'              => $options['db_prefix'],
                'use_rewrites'           => $this->_getFlagValue($options['use_rewrites']),
                'use_secure'             => $this->_getFlagValue($options['use_secure']),
                'unsecure_base_url'      => $options['url'],
                'secure_base_url'        => $options['secure_base_url'],
                'use_secure_admin'       => $this->_getFlagValue($options['use_secure_admin']),
                'session_save'           => $this->_checkSessionSave($options['session_save']),
                'backend_frontname'      => $this->_checkBackendFrontname($options['backend_frontname']),
                'admin_no_form_key'      => $this->_getFlagValue($options['admin_no_form_key']),
                'skip_url_validation'    => $this->_getFlagValue($options['skip_url_validation']),
                'enable_charts'          => $this->_getFlagValue($options['enable_charts']),
                'order_increment_prefix' => $options['order_increment_prefix'],
            ));

            /**
             * Primary admin user
             */
            $this->_installerData->setAdminData(array(
                'firstname'         => $options['admin_firstname'],
                'lastname'          => $options['admin_lastname'],
                'email'             => $options['admin_email'],
                'username'          => $options['admin_username'],
                'password'          => $options['admin_password'],
            ));

            $installer = $this->_getInstaller();

            /**
             * Install configuration
             */
            $installer->installConfig($this->_installerData->getConfigData());

            if (!empty($options['cleanup_database'])) {
                $this->_cleanUpDatabase();
            }

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Install database
             */
            $installer->installDb();

            if ($this->hasErrors()) {
                return false;
            }

            // apply data updates
            $this->_dbUpdater->updateData();

            /**
             * Create primary administrator user & install encryption key
             */
            $encryptionKey = !empty($options['encryption_key']) ? $options['encryption_key'] : null;
            $encryptionKey = $installer->getValidEncryptionKey($encryptionKey);
            $installer->createAdministrator($this->_installerData->getAdminData());
            $installer->installEncryptionKey($encryptionKey);

            /**
             * Installation finish
             */
            $installer->finish();

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Change directories mode to be writable by apache user
             */
            $this->_filesystem
                ->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR)
                ->changePermissions('', 0777);

            return $encryptionKey;
        } catch (\Exception $e) {
            if ($e instanceof \Magento\Core\Exception) {
                foreach ($e->getMessages(\Magento\Message\MessageInterface::TYPE_ERROR) as $errorMessage) {
                    $this->addError($errorMessage);
                }
            } else {
                $this->addError('ERROR: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Cleanup database use system configuration
     *
     * @return void
     */
    protected function _cleanUpDatabase()
    {
        $modelName = 'Magento\Install\Model\Installer\Db\Mysql4';
        /** @var $resourceModel \Magento\Install\Model\Installer\Db\AbstractDb */
        $resourceModel = $this->_objectManager->get($modelName);
        $resourceModel->cleanUpDatabase();
    }

    /**
     * Uninstall the application
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!$this->_appState->isInstalled()) {
            return false;
        }

        $this->_cleanUpDatabase();

        /* Remove temporary directories and local.xml */
        $varDirectory = $this->_filesystem->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR);
        foreach ($varDirectory->read() as $path) {
            if ($varDirectory->isDirectory($path)) {
                $varDirectory->delete($path);
            }
        }
        $this->_filesystem->getDirectoryWrite(\Magento\App\Filesystem::CONFIG_DIR)->delete('local.xml');
        return true;
    }

    /**
     * Retrieve available locale codes
     *
     * @return array
     */
    public function getAvailableLocales()
    {
        return $this->_locale->getOptionLocales();
    }

    /**
     * Retrieve available currency codes
     *
     * @return array
     */
    public function getAvailableCurrencies()
    {
        return $this->_locale->getOptionCurrencies();
    }

    /**
     * Retrieve available timezone codes
     *
     * @return array
     */
    public function getAvailableTimezones()
    {
        return $this->_locale->getOptionTimezones();
    }

    /**
     * Retrieve available installation options
     *
     * @return array
     */
    public function getAvailableInstallOptions()
    {
        $result = array();
        foreach ($this->_installOptions as $optionName => $optionInfo) {
            $result[$optionName] = ($optionInfo['required'] ? 'required' : 'optional');
        }
        return $result;
    }
}
