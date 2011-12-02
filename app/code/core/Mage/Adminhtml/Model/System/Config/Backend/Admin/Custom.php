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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml backend model for "Custom Admin URL" option
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom extends Mage_Core_Model_Config_Data
{
    const CONFIG_SCOPE                      = 'stores';
    const CONFIG_SCOPE_ID                   = 0;

    const XML_PATH_UNSECURE_BASE_URL        = 'web/unsecure/base_url';
    const XML_PATH_SECURE_BASE_URL          = 'web/secure/base_url';
    const XML_PATH_UNSECURE_BASE_LINK_URL   = 'web/unsecure/base_link_url';
    const XML_PATH_SECURE_BASE_LINK_URL     = 'web/secure/base_link_url';

    /**
     * Validate value before save
     *
     * @return Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();

        if (!empty($value) && substr($value, -2) !== '}}') {
            $value = rtrim($value, '/').'/';
        }

        $this->setValue($value);
        return $this;
    }

    /**
     * Change secure/unsecure base_url after use_custom_url was modified
     *
     * @return Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom
     */
    public function _afterSave()
    {
        $useCustomUrl = $this->getData('groups/url/fields/use_custom/value');
        $value = $this->getValue();

        if ($useCustomUrl == 1 && empty($value)) {
            return $this;
        }

        if ($useCustomUrl == 1) {
            Mage::getConfig()->saveConfig(
                self::XML_PATH_SECURE_BASE_URL,
                $value,
                self::CONFIG_SCOPE,
                self::CONFIG_SCOPE_ID
            );
            Mage::getConfig()->saveConfig(
                self::XML_PATH_UNSECURE_BASE_URL,
                $value,
                self::CONFIG_SCOPE,
                self::CONFIG_SCOPE_ID
            );
        } else {
            Mage::getConfig()->deleteConfig(
                self::XML_PATH_SECURE_BASE_URL,
                self::CONFIG_SCOPE,
                self::CONFIG_SCOPE_ID
            );
            Mage::getConfig()->deleteConfig(
                self::XML_PATH_UNSECURE_BASE_URL,
                self::CONFIG_SCOPE,
                self::CONFIG_SCOPE_ID
            );
        }

        return $this;
    }
}
