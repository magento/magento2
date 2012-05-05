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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Console installer
 * @category   Mage
 * @package    Mage_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Install_Model_Installer_Console extends Mage_Install_Model_Installer_Abstract
{

    /**
     * Available options
     *
     * @var array
     */
    protected $_options;

    /**
     * Script arguments
     *
     * @var array
     */
    protected $_args = array();

    /**
     * Installer data model to store data between installations steps
     *
     * @var Mage_Install_Model_Installer_Data|Mage_Install_Model_Session
     */
    protected $_dataModel;

    /**
     * Current application
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Get available options list
     *
     * @return array
     */
    protected function _getOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                'license_agreement_accepted'    => array('required' => true, 'comment' => ''),
                'locale'              => array('required' => true, 'comment' => ''),
                'timezone'            => array('required' => true, 'comment' => ''),
                'default_currency'    => array('required' => true, 'comment' => ''),
                'db_model'            => array('comment' => ''),
                'db_host'             => array('required' => true, 'comment' => ''),
                'db_name'             => array('required' => true, 'comment' => ''),
                'db_user'             => array('required' => true, 'comment' => ''),
                'db_pass'             => array('comment' => ''),
                'db_prefix'           => array('comment' => ''),
                'url'                 => array('required' => true, 'comment' => ''),
                'skip_url_validation' => array('comment' => ''),
                'use_rewrites'      => array('required' => true, 'comment' => ''),
                'use_secure'        => array('required' => true, 'comment' => ''),
                'secure_base_url'   => array('required' => true, 'comment' => ''),
                'use_secure_admin'  => array('required' => true, 'comment' => ''),
                'admin_lastname'    => array('required' => true, 'comment' => ''),
                'admin_firstname'   => array('required' => true, 'comment' => ''),
                'admin_email'       => array('required' => true, 'comment' => ''),
                'admin_username'    => array('required' => true, 'comment' => ''),
                'admin_password'    => array('required' => true, 'comment' => ''),
                'encryption_key'    => array('comment' => ''),
                'session_save'      => array('comment' => ''),
                'admin_frontname'   => array('comment' => ''),
                'enable_charts'     => array('comment' => ''),
            );
        }
        return $this->_options;
    }

    /**
     * Set and validate arguments
     *
     * @param array $args
     * @return boolean
     */
    public function setArgs($args = null)
    {
        if (empty($args)) {
            // take server args
            $args = $_SERVER['argv'];
        }

        /**
         * Parse arguments
         */
        $currentArg = false;
        $match = false;
        foreach ($args as $arg) {
            if (preg_match('/^--(.*)$/', $arg, $match)) {
                // argument name
                $currentArg = $match[1];
                // in case if argument doen't need a value
                $args[$currentArg] = true;
            } else {
                // argument value
                if ($currentArg) {
                    $args[$currentArg] = $arg;
                }
                $currentArg = false;
            }
        }

        if (isset($args['get_options'])) {
            $this->printOptions();
            return false;
        }

        /**
         * Check required arguments
         */
        foreach ($this->_getOptions() as $name => $option) {
            if (isset($option['required']) && $option['required'] && !isset($args[$name])) {
                $error = 'ERROR: ' . 'You should provide the value for --' . $name . ' parameter';
                if (!empty($option['comment'])) {
                    $error .= ': ' . $option['comment'];
                }
                $this->addError($error);
            }
        }

        if ($this->hasErrors()) {
            return false;
        }

        /**
         * Validate license aggreement acceptance
         */
        if (!$this->_checkFlag($args['license_agreement_accepted'])) {
            $this->addError('ERROR: You have to accept Magento license agreement terms and conditions to continue installation');
            return false;
        }

        /**
         * Set args values
         */
        foreach ($this->_getOptions() as $name => $option) {
            $this->_args[$name] = isset($args[$name]) ? $args[$name] : '';
        }

        return true;
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
     * Check flag value
     *
     * Returns true for 'yes', 1, 'true'
     * Case insensitive
     *
     * @param string $value
     * @return boolean
     */
    protected function _checkFlag($value)
    {
        $res = (1 == $value)
            || preg_match('/^(yes|y|true)$/i', $value);
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
     * Get encryption key from data model
     *
     * @return string
     */
    public function getEncryptionKey()
    {
        return $this->_getDataModel()->getEncryptionKey();
    }

    /**
     * Init installation
     *
     * @param Mage_Core_Model_App $app
     * @return boolean
     */
    public function init(Mage_Core_Model_App $app)
    {
        $this->_app = $app;
        $this->_getInstaller()->setDataModel($this->_getDataModel());

        /**
         * Check if already installed
         */
        if (Mage::isInstalled()) {
            $this->addError('ERROR: Magento is already installed');
            return false;
        }

        return true;
    }

    /**
     * Prepare data ans save it in data model
     *
     * @return Mage_Install_Model_Installer_Console
     */
    protected function _prepareData()
    {
        /**
         * Locale settings
         */
        $this->_getDataModel()->setLocaleData(array(
            'locale'            => $this->_args['locale'],
            'timezone'          => $this->_args['timezone'],
            'currency'          => $this->_args['default_currency'],
        ));

        /**
         * Database and web config
         */
        $this->_getDataModel()->setConfigData(array(
            'db_model'            => $this->_args['db_model'],
            'db_host'             => $this->_args['db_host'],
            'db_name'             => $this->_args['db_name'],
            'db_user'             => $this->_args['db_user'],
            'db_pass'             => $this->_args['db_pass'],
            'db_prefix'           => $this->_args['db_prefix'],
            'use_rewrites'        => $this->_checkFlag($this->_args['use_rewrites']),
            'use_secure'          => $this->_checkFlag($this->_args['use_secure']),
            'unsecure_base_url'   => $this->_args['url'],
            'secure_base_url'     => $this->_args['secure_base_url'],
            'use_secure_admin'    => $this->_checkFlag($this->_args['use_secure_admin']),
            'session_save'        => $this->_checkSessionSave($this->_args['session_save']),
            'admin_frontname'     => $this->_checkAdminFrontname($this->_args['admin_frontname']),
            'skip_url_validation' => $this->_checkFlag($this->_args['skip_url_validation']),
            'enable_charts'       => $this->_checkFlag($this->_args['enable_charts']),
        ));

        /**
         * Primary admin user
         */
        $this->_getDataModel()->setAdminData(array(
            'firstname'         => $this->_args['admin_firstname'],
            'lastname'          => $this->_args['admin_lastname'],
            'email'             => $this->_args['admin_email'],
            'username'          => $this->_args['admin_username'],
            'new_password'      => $this->_args['admin_password'],
        ));

        return $this;
    }

    /**
     * Install Magento
     *
     * @return boolean
     */
    public function install()
    {
        try {

            /**
             * Check if already installed
             */
            if (Mage::isInstalled()) {
                $this->addError('ERROR: Magento is already installed');
                return false;
            }

            /**
             * Skip URL validation, if set
             */
            $this->_getDataModel()->setSkipUrlValidation($this->_args['skip_url_validation']);
            $this->_getDataModel()->setSkipBaseUrlValidation($this->_args['skip_url_validation']);

            /**
             * Prepare data
             */
            $this->_prepareData();

            if ($this->hasErrors()) {
                return false;
            }

            $installer = $this->_getInstaller();

            /**
             * Install configuration
             */
            $installer->installConfig($this->_getDataModel()->getConfigData()); // TODO fix wizard and simplify this everywhere

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Reinitialize configuration (to use new config data)
             */

            $this->_app->cleanCache();
            Mage::getConfig()->reinit();

            /**
             * Install database
             */
            $installer->installDb();

            if ($this->hasErrors()) {
                return false;
            }

            // apply data updates
            Mage_Core_Model_Resource_Setup::applyAllDataUpdates();

            /**
             * Validate entered data for administrator user
             */
            $user = $installer->validateAndPrepareAdministrator($this->_getDataModel()->getAdminData());

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Prepare encryption key and validate it
             */
            $encryptionKey = empty($this->_args['encryption_key'])
                ? md5(Mage::helper('Mage_Core_Helper_Data')->getRandomString(10))
                : $this->_args['encryption_key'];
            $this->_getDataModel()->setEncryptionKey($encryptionKey);
            $installer->validateEncryptionKey($encryptionKey);

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Create primary administrator user
             */
            $installer->createAdministrator($user);

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Save encryption key or create if empty
             */
            $installer->installEnryptionKey($encryptionKey);

            if ($this->hasErrors()) {
                return false;
            }

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
            @chmod('var/cache', 0777);
            @chmod('var/session', 0777);

        } catch (Exception $e) {
            $this->addError('ERROR: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Print available currency, locale and timezone options
     *
     * @return Mage_Install_Model_Installer_Console
     */
    public function printOptions()
    {
        $options = array(
            'locale'    => $this->_app->getLocale()->getOptionLocales(),
            'currency'  => $this->_app->getLocale()->getOptionCurrencies(),
            'timezone'  => $this->_app->getLocale()->getOptionTimezones(),
        );
        var_export($options);
        return $this;
    }

    /**
     * Check if installer is run in shell, and redirect if run on web
     *
     * @param string $url fallback url to redirect to
     * @return boolean
     */
    public function checkConsole($url=null)
    {
        if (defined('STDIN') && defined('STDOUT') && (defined('STDERR'))) {
            return true;
        }
        if (is_null($url)) {
            $url = preg_replace('/install\.php/i', '', Mage::getBaseUrl());
            $url = preg_replace('/\/\/$/', '/', $url);
        }
        header('Location: ' . $url);
        return false;
    }

}
