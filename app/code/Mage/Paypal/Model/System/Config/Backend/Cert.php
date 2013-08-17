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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend model for saving certificate file in case of using certificate based authentication
 */
class Mage_Paypal_Model_System_Config_Backend_Cert extends Mage_Core_Model_Config_Data
{
    /**
     * Process additional data before save config
     *
     * @return Mage_Paypal_Model_System_Config_Backend_Cert
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value) && !empty($value['delete'])) {
            $this->setValue('');
            Mage::getModel('Mage_Paypal_Model_Cert')->loadByWebsite($this->getScopeId())->delete();
        }

        if (!isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])) {
            return $this;
        }
        $tmpPath = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
        if ($tmpPath && file_exists($tmpPath)) {
            if (!filesize($tmpPath)) {
                Mage::throwException(Mage::helper('Mage_Paypal_Helper_Data')->__('The PayPal certificate file is empty.'));
            }
            $this->setValue($_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value']);
            $content = Mage::helper('Mage_Core_Helper_Data')->encrypt(file_get_contents($tmpPath));
            Mage::getModel('Mage_Paypal_Model_Cert')->loadByWebsite($this->getScopeId())
                ->setContent($content)
                ->save();
        }
        return $this;
    }

    /**
     * Process object after delete data
     *
     * @return Mage_Paypal_Model_System_Config_Backend_Cert
     */
    protected function _afterDelete()
    {
        Mage::getModel('Mage_Paypal_Model_Cert')->loadByWebsite($this->getScopeId())->delete();
        return $this;
    }
}
