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
 * Environment installer
 *
 * @category   Mage
 * @package    Mage_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Install_Model_Installer_Env extends Mage_Install_Model_Installer_Abstract
{
    public function __construct() {}

    public function install()
    {
        if (!$this->_checkPhpExtensions()) {
            throw new Exception();
        }
        return $this;
    }

    protected function _checkPhpExtensions()
    {
        $res = true;
        $config = Mage::getSingleton('Mage_Install_Model_Config')->getExtensionsForCheck();
        foreach ($config as $extension => $info) {
            if (!empty($info) && is_array($info)) {
                $res = $this->_checkExtension($info) && $res;
            }
            else {
                $res = $this->_checkExtension($extension) && $res;
            }
        }
        return $res;
    }

    protected function _checkExtension($extension)
    {
        if (is_array($extension)) {
            $oneLoaded = false;
            foreach ($extension as $item) {
                if (extension_loaded($item)) {
                    $oneLoaded = true;
                }
            }

            if (!$oneLoaded) {
                Mage::getSingleton('Mage_Install_Model_Session')->addError(
                    Mage::helper('Mage_Install_Helper_Data')->__('One of PHP Extensions "%s" must be loaded.', implode(',', $extension))
                );
                return false;
            }
        }
        elseif (!extension_loaded($extension)) {
            Mage::getSingleton('Mage_Install_Model_Session')->addError(
                Mage::helper('Mage_Install_Helper_Data')->__('PHP extension "%s" must be loaded.', $extension)
            );
            return false;
        }
        else {
            /*Mage::getSingleton('Mage_Install_Model_Session')->addError(
                Mage::helper('Mage_Install_Helper_Data')->__("PHP Extension '%s' loaded", $extension)
            );*/
        }
        return true;
    }
}
