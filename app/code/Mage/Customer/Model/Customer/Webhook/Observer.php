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
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer webhook observer
 */
class Mage_Customer_Model_Customer_Webhook_Observer
{
    public function dispatchCustomerEvent($observer)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getEvent()->getCustomer();

        try {
            if ($customer->getUpdatedAt() === $customer->getCreatedAt()) {
                Mage::helper('Mage_Webhook_Helper_Data')
                    ->dispatchEvent('customer/created', array('object' => $customer));
            } else if ($customer->hasDataChanges()) {
                Mage::helper('Mage_Webhook_Helper_Data')
                    ->dispatchEvent('customer/updated', array('object' => $customer));
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function dispatchCustomerDeletedEvent($observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        try {
            Mage::helper('Mage_Webhook_Helper_Data')->dispatchEvent('customer/deleted', array('customer' => $customer));
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
