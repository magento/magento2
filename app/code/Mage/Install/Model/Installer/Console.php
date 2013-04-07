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
 * @category    Mage
 * @package     Mage_Install
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento application console installer
 */
class Mage_Install_Model_Installer_Console extends Mage_Install_Model_Installer_Abstract
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
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * Installer data model to store data between installations steps
     *
     * @var Mage_Install_Model_Installer_Data|Mage_Install_Model_Session
     */
    protected $_dataModel;

    /**
     * Resource config
     *
     * @var Mage_Core_Model_Config_Resource
     */
    protected $_resourceConfig;

    /**
     * DB updater
     *
     * @var Mage_Core_Model_Db_UpdaterInterface
     */
    protected $_dbUpdater;

    /**
     * @param Mage_Core_Model_Config_Resource $resourceConfig
     * @param Mage_Core_Model_Db_UpdaterInterface $daUpdater
     */
    public function __construct(
        Mage_Core_Model_Config_Resource $resourceConfig,
        Mage_Core_Model_Db_UpdaterInterface $daUpdater,
        Magento_Filesystem $filesystem
    ) {
        $this->_resourceConfig = $resourceConfig;
        $this->_dbUpdater = $daUpdater;
        $this->_getInstaller()->setDataModel($this->_getDataModel());
        $this->_filesystem = $filesystem;
    }

    /**
     * Retrieve validated installation options
     *
     * @param array $options
     * @return array|boolean
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
     * @return Mage_Install_Model_Installer_Console
     */
    public function addError($error)
    {
        $this->_getDataModel()->addError($error);
        return $this;
    }

    /**
     * Check if there were any errors
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return (count($this->_getDataModel()->getErrors()) > 0);
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_getDataModel()->getErrors();
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
     * Get data model (used to store data between installation steps
     *
     * @return Mage_Install_Model_Installer_Data
     */
    protected function _getDataModel()
    {
        if (is_null($this->_dataModel)) {
            $this->_dataModel = Mage::getModel('Mage_Install_Model_Installer_Data');
        }
        return $this->_dataModel;
    }

    /**
     * Install Magento
     *
     * @param array $options
     * @return string|boolean
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
            if (Mage::isInstalled()) {
                $this->addError('ERROR: Magento is already installed.');
                return false;
            }

            /**
             * Skip URL validation, if set
             */
            $this->_getDataModel()->setSkipUrlValidation($options['skip_url_validation']);
            $this->_getDataModel()->setSkipBaseUrlValidation($options['skip_url_validation']);

            /**
             * Locale settings
             */
            $this->_getDataModel()->setLocaleData(array(
                'locale'            => $options['locale'],
                'timezone'          => $options['timezone'],
                'currency'          => $options['default_currency'],
            ));

            /**
             * Database and web config
             */
            $this->_getDataModel()->setConfigData(array(
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
            $this->_getDataModel()->setAdminData(array(
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
            $installer->installConfig($this->_getDataModel()->getConfigData());

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
            $installer->createAdministrator($this->_getDataModel()->getAdminData());
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
            $this->_filesystem->changePermissions(Mage::getBaseDir('var'), 0777, true);
            return $encryptionKey;
        } catch (Exception $e) {
            if ($e instanceof Mage_Core_Exception) {
                foreach ($e->getMessages(Mage_Core_Model_Message::ERROR) as $errorMessage) {
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
     */
    protected function _cleanUpDatabase()
    {
        $dbConfig = $this->_resourceConfig
            ->getResourceConnectionConfig(Mage_Core_Model_Resource::DEFAULT_SETUP_RESOURCE);
        $modelName = 'Mage_Install_Model_Installer_Db_' . ucfirst($dbConfig->model);

        if (!class_exists($modelName)) {
            $this->addError('Database uninstall is not supported for the ' . ucfirst($dbConfig->model) . '.');
            return false;
        }

        /** @var $resourceModel Mage_Install_Model_Installer_Db_Abstract */
        $resourceModel = Mage::getModel($modelName);
        $resourceModel->cleanUpDatabase($dbConfig);
    }

    /**
     * Uninstall the application
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!Mage::isInstalled()) {
            return false;
        }

        $this->_cleanUpDatabase();

        /* Remove temporary directories and local.xml */
        foreach (glob(Mage::getBaseDir(Mage_Core_Model_Dir::VAR_DIR) . '/*', GLOB_ONLYDIR) as $dir) {
            $this->_filesystem->delete($dir);
        }
        $this->_filesystem->delete(Mage::getBaseDir(Mage_Core_Model_Dir::CONFIG) . DIRECTORY_SEPARATOR . '/local.xml');
        return true;
    }

    /**
     * Retrieve available locale codes
     *
     * @return array
     */
    public function getAvailableLocales()
    {
        return Mage::app()->getLocale()->getOptionLocales();
    }

    /**
     * Retrieve available currency codes
     *
     * @return array
     */
    public function getAvailableCurrencies()
    {
        return Mage::app()->getLocale()->getOptionCurrencies();
    }

    /**
     * Retrieve available timezone codes
     *
     * @return array
     */
    public function getAvailableTimezones()
    {
        return Mage::app()->getLocale()->getOptionTimezones();
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
