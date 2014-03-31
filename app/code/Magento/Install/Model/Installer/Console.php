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
    protected $installParameters = array();

    /**
     * Required parameters with descriptions
     *
     * @var array
     */
    protected $requiredParameters = array(
        'license_agreement_accepted' => 'Accept licence. See LICENSE*.txt. Flag value.',
        'locale' => 'Locale to use. Run with --show_locales for full list',
        'timezone' => 'Time zone to use. Run with --show_timezones for full list',
        'default_currency' => 'Default currency. Run with --show_currencies for full list',
        'db_host' => 'IP or name of your DB host',
        'db_name' => 'Database name',
        'db_user' => 'Database user name',
        'url' => 'Instance URL. For example, "http://myinstance.com"',
        'use_rewrites' => 'Use web server rewrites. Flag value',
        'use_secure' => 'Use https(ssl) protocol. Flag value',
        'secure_base_url' => 'Full secure URL if use_secure enabled. For example "https://myinstance.com"',
        'use_secure_admin' => 'Use secure protocol for backend. Flag value',
        'admin_lastname' => 'Admin user last name',
        'admin_firstname' => 'Admin user first name',
        'admin_email' => 'Admin email',
        'admin_username' => 'Admin login',
        'admin_password' => 'Admin password'
    );

    /**
     * Optional parameters with descriptions
     *
     * @var array
     */
    protected $optionalParameters = array(
        'db_model' => 'DB driver. "mysql4" is default and the only supported now',
        'db_pass' => 'DB password. Empty by default',
        'db_prefix' => 'Use prefix for tables of this installation. Empty by default',
        'skip_url_validation' => 'Skip URL validation on installation. Flag value. Validate by default',
        'admin_no_form_key' => 'Disable the form key protection on the back-end. Flag value. Enabled by default',
        'encryption_key' => 'Key to encrypt sensitive data. Auto-generated if empty',
        'session_save' => 'Where session data will be stored. "files"(default) or "db"',
        'backend_frontname' => 'Backend URL path. "backend" by default',
        'enable_charts' => 'Enable charts on backend dashboard. Flag value. Enabled by default',
        'order_increment_prefix' => 'Order number prefix. Empty by default.',
        'cleanup_database' => 'Clean up database before installation. Flag value. Disabled by default'
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
     * Locale Lists
     *
     * @var \Magento\Locale\ListsInterface
     */
    protected $_localeLists;

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
     * @param \Magento\Locale\ListsInterface $localeLists
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Install\Model\Installer $installer,
        \Magento\App\Resource\Config $resourceConfig,
        \Magento\Module\UpdaterInterface $dbUpdater,
        \Magento\App\Filesystem $filesystem,
        \Magento\Install\Model\Installer\Data $installerData,
        \Magento\App\State $appState,
        \Magento\Locale\ListsInterface $localeLists,
        \Magento\ObjectManager $objectManager
    ) {
        parent::__construct($installer);
        $this->_resourceConfig = $resourceConfig;
        $this->_dbUpdater = $dbUpdater;
        $this->_filesystem = $filesystem;
        $this->_installerData = $installerData;
        $this->_installer->setDataModel($this->_installerData);
        $this->_appState = $appState;
        $this->_localeLists = $localeLists;
        $this->_objectManager = $objectManager;
        $this->installParameters = array_keys($this->requiredParameters + $this->optionalParameters);
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
        foreach (array_keys($this->requiredParameters) as $optionName) {
            if (!isset($options[$optionName])) {
                $this->addError("ERROR: installation parameter '{$optionName}' is required.");
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
        foreach ($this->installParameters as $optionName) {
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
        return count($this->_installerData->getErrors()) > 0;
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
        $res = 1 == $value || preg_match('/^(yes|y|true)$/i', $value);
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
            $this->_installerData->setLocaleData(
                array(
                    'locale' => $options['locale'],
                    'timezone' => $options['timezone'],
                    'currency' => $options['default_currency']
                )
            );

            /**
             * Database and web config
             */
            $this->_installerData->setConfigData(
                array(
                    'db_model' => $options['db_model'],
                    'db_host' => $options['db_host'],
                    'db_name' => $options['db_name'],
                    'db_user' => $options['db_user'],
                    'db_pass' => $options['db_pass'],
                    'db_prefix' => $options['db_prefix'],
                    'use_rewrites' => $this->_getFlagValue($options['use_rewrites']),
                    'use_secure' => $this->_getFlagValue($options['use_secure']),
                    'unsecure_base_url' => $options['url'],
                    'secure_base_url' => $options['secure_base_url'],
                    'use_secure_admin' => $this->_getFlagValue($options['use_secure_admin']),
                    'session_save' => $this->_checkSessionSave($options['session_save']),
                    'backend_frontname' => $this->_checkBackendFrontname($options['backend_frontname']),
                    'admin_no_form_key' => $this->_getFlagValue($options['admin_no_form_key']),
                    'skip_url_validation' => $this->_getFlagValue($options['skip_url_validation']),
                    'enable_charts' => $this->_getFlagValue($options['enable_charts']),
                    'order_increment_prefix' => $options['order_increment_prefix']
                )
            );

            /**
             * Primary admin user
             */
            $this->_installerData->setAdminData(
                array(
                    'firstname' => $options['admin_firstname'],
                    'lastname' => $options['admin_lastname'],
                    'email' => $options['admin_email'],
                    'username' => $options['admin_username'],
                    'password' => $options['admin_password']
                )
            );

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
            $this->_filesystem->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR)->changePermissions('', 0777);

            return $encryptionKey;
        } catch (\Exception $e) {
            if ($e instanceof \Magento\Model\Exception) {
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
        return $this->_localeLists->getOptionLocales();
    }

    /**
     * Retrieve available currency codes
     *
     * @return array
     */
    public function getAvailableCurrencies()
    {
        return $this->_localeLists->getOptionCurrencies();
    }

    /**
     * Retrieve available timezone codes
     *
     * @return array
     */
    public function getAvailableTimezones()
    {
        return $this->_localeLists->getOptionTimezones();
    }

    /**
     * Retrieve required installation params
     *
     * @return array
     */
    public function getRequiredParams()
    {

        return $this->requiredParameters;
    }

    /**
     * Get optional installation parameters
     * @return array
     */
    public function getOptionalParams()
    {
        return $this->optionalParameters;
    }
}
