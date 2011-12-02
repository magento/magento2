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
 * Order control model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_Sales_Order
{
    /**
     * Retrieve adminhtml session singleton
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session');
    }

    public function checkRelation(Mage_Sales_Model_Order $order)
    {
        /**
         * Check customer existing
         */
        $customer = Mage::getModel('Mage_Customer_Model_Customer')->load($order->getCustomerId());
        if (!$customer->getId()) {
            $this->_getSession()->addNotice(
                Mage::helper('Mage_Adminhtml_Helper_Data')->__(' The customer does not exist in the system anymore.')
            );
        }

        /**
         * Check Item products existing
         */
        $productIds = array();
        foreach ($order->getAllItems() as $item) {
            $productIds[] = $item->getProductId();
        }

        $productCollection = Mage::getModel('Mage_Catalog_Model_Product')->getCollection()
            ->addIdFilter($productIds)
            ->load();

        $hasBadItems = false;
        foreach ($order->getAllItems() as $item) {
            if (!$productCollection->getItemById($item->getProductId())) {
                $this->_getSession()->addError(
                   Mage::helper('Mage_Adminhtml_Helper_Data')->__('The item %s (SKU %s) does not exist in the catalog anymore.',
                       $item->getName(),
                       $item->getSku()
                ));
                $hasBadItems = true;
            }
        }
        if ($hasBadItems) {
            $this->_getSession()->addError(
                Mage::helper('Mage_Adminhtml_Helper_Data')->__('Some of the ordered items do not exist in the catalog anymore and will be removed if you try to edit the order.')
            );
        }
        return $this;
    }

}

