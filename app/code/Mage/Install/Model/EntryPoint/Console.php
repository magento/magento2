<?php
/**
 * Console entry point
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Install_Model_EntryPoint_Console extends Mage_Core_Model_EntryPointAbstract
{
    /**
     * @param string $baseDir
     * @param array $params
     */
    public function __construct($baseDir, array $params = array())
    {
        $this->_params = $this->_buildInitParams($params);
        $config = new Mage_Core_Model_Config_Primary($baseDir, $this->_params);
        parent::__construct($config);
    }

    /**
     * Customize application init parameters
     *
     * @param array $args
     * @return array
     */
    protected function _buildInitParams(array $args)
    {
        if (!empty($args[Mage_Install_Model_Installer_Console::OPTION_URIS])) {
            $args[MAGE::PARAM_APP_URIS] =
                unserialize(base64_decode($args[Mage_Install_Model_Installer_Console::OPTION_URIS]));
        }
        if (!empty($args[Mage_Install_Model_Installer_Console::OPTION_DIRS])) {
            $args[Mage::PARAM_APP_DIRS] =
                unserialize(base64_decode($args[Mage_Install_Model_Installer_Console::OPTION_DIRS]));
        }
        return $args;
    }

    /**
     * Run http application
     */
    public function processRequest()
    {
        /**
         * @var $installer Mage_Install_Model_Installer_Console
         */
        $installer = $this->_objectManager->create(
            'Mage_Install_Model_Installer_Console',
            array('installArgs' => $this->_params)
        );
        if (isset($this->_params['show_locales'])) {
            var_export($installer->getAvailableLocales());
        } else if (isset($this->_params['show_currencies'])) {
            var_export($installer->getAvailableCurrencies());
        } else if (isset($this->_params['show_timezones'])) {
            var_export($installer->getAvailableTimezones());
        } else if (isset($this->_params['show_install_options'])) {
            var_export($installer->getAvailableInstallOptions());
        } else {
            if (isset($this->_params['config']) && file_exists($this->_params['config'])) {
                $config = (array) include($this->_params['config']);
                $this->_params = array_merge((array)$config, $this->_params);
            }
            $isUninstallMode = isset($this->_params['uninstall']);
            if ($isUninstallMode) {
                $result = $installer->uninstall();
            } else {
                $result = $installer->install($this->_params);
            }
            if (!$installer->hasErrors()) {
                if ($isUninstallMode) {
                    $msg = $result ?
                        'Uninstalled successfully' :
                        'Ignoring attempt to uninstall non-installed application';
                } else {
                    $msg = 'Installed successfully' . ($result ? ' (encryption key "' . $result . '")' : '');
                }
                echo $msg . PHP_EOL;
            } else {
                echo implode(PHP_EOL, $installer->getErrors()) . PHP_EOL;
                exit(1);
            }
        }
    }
}
