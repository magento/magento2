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
 * Customer Reset Password Link Expiration period backend model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_System_Config_Backend_Customer_Password_Link_Expirationperiod
    extends Mage_Core_Model_Config_Data
{
    /**
     * Validate expiration period value before saving
     *
     * @return Mage_Adminhtml_Model_System_Config_Backend_Customer_Password_Link_Expirationperiod
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $resetPasswordLinkExpirationPeriod = (int) $this->getValue();
        // This value must be greater than 0
        if ($resetPasswordLinkExpirationPeriod < 1) {
            $resetPasswordLinkExpirationPeriod = (int) $this->getOldValue();
        }
        $this->setValue((string) $resetPasswordLinkExpirationPeriod);
        return $this;
    }
}
