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
 * AdminNotification update frequency source
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_System_Config_Source_Notification_Frequency
{
    public function toOptionArray()
    {
        return array(
            1   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('1 Hour'),
            2   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('2 Hours'),
            6   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('6 Hours'),
            12  => Mage::helper('Mage_Adminhtml_Helper_Data')->__('12 Hours'),
            24  => Mage::helper('Mage_Adminhtml_Helper_Data')->__('24 Hours')
        );
    }
}
