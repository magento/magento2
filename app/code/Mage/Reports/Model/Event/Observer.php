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
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Reports Event observer model
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Event_Observer
{
    /**
     * Abstract Event obeserver logic
     *
     * Save event
     *
     * @param int $eventTypeId
     * @param int $objectId
     * @param int $subjectId
     * @param int $subtype
     * @return Mage_Reports_Model_Event_Observer
     */
    protected function _event($eventTypeId, $objectId, $subjectId = null, $subtype = 0)
    {
        if (is_null($subjectId)) {
            if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
                $customer = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer();
                $subjectId = $customer->getId();
            }
            else {
                $subjectId = Mage::getSingleton('Mage_Log_Model_Visitor')->getId();
                $subtype = 1;
            }
        }

        $eventModel = Mage::getModel('Mage_Reports_Model_Event');
        $storeId    = Mage::app()->getStore()->getId();
        $eventModel
            ->setEventTypeId($eventTypeId)
            ->setObjectId($objectId)
            ->setSubjectId($subjectId)
            ->setSubtype($subtype)
            ->setStoreId($storeId);
        $eventModel->save();

        return $this;
    }

    /**
     * Customer login action
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {
        if (!Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            return $this;
        }

        $visitorId  = Mage::getSingleton('Mage_Log_Model_Visitor')->getId();
        $customerId = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId();
        $eventModel = Mage::getModel('Mage_Reports_Model_Event');
        $eventModel->updateCustomerType($visitorId, $customerId);

        Mage::getModel('Mage_Reports_Model_Product_Index_Compared')
            ->updateCustomerFromVisitor()
            ->calculate();
        Mage::getModel('Mage_Reports_Model_Product_Index_Viewed')
            ->updateCustomerFromVisitor()
            ->calculate();

        return $this;
    }

    /**
     * Customer logout processing
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function customerLogout(Varien_Event_Observer $observer)
    {
        Mage::getModel('Mage_Reports_Model_Product_Index_Compared')
            ->purgeVisitorByCustomer()
            ->calculate();
        Mage::getModel('Mage_Reports_Model_Product_Index_Viewed')
            ->purgeVisitorByCustomer()
            ->calculate();
        return $this;
    }

    /**
     * View Catalog Product action
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function catalogProductView(Varien_Event_Observer $observer)
    {
        $productId = $observer->getEvent()->getProduct()->getId();

        Mage::getModel('Mage_Reports_Model_Product_Index_Viewed')
            ->setProductId($productId)
            ->save()
            ->calculate();

        return $this->_event(Mage_Reports_Model_Event::EVENT_PRODUCT_VIEW, $productId);
    }

    /**
     * Send Product link to friends action
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function sendfriendProduct(Varien_Event_Observer $observer)
    {
        return $this->_event(Mage_Reports_Model_Event::EVENT_PRODUCT_SEND,
            $observer->getEvent()->getProduct()->getId()
        );
    }

    /**
     * Remove Product from Compare Products action
     *
     * Reset count of compared products cache
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function catalogProductCompareRemoveProduct(Varien_Event_Observer $observer)
    {
        Mage::getModel('Mage_Reports_Model_Product_Index_Compared')->calculate();

        return $this;
    }

    /**
     * Remove All Products from Compare Products
     *
     * Reset count of compared products cache
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function catalogProductCompareClear(Varien_Event_Observer $observer)
    {
        Mage::getModel('Mage_Reports_Model_Product_Index_Compared')->calculate();

        return $this;
    }

    /**
     * Add Product to Compare Products List action
     *
     * Reset count of compared products cache
     *
     * @param Varien_Event_Observer $observer
     * @return unknown
     */
    public function catalogProductCompareAddProduct(Varien_Event_Observer $observer)
    {
        $productId = $observer->getEvent()->getProduct()->getId();

        Mage::getModel('Mage_Reports_Model_Product_Index_Compared')
            ->setProductId($productId)
            ->save()
            ->calculate();

        return $this->_event(Mage_Reports_Model_Event::EVENT_PRODUCT_COMPARE, $productId);
    }

    /**
     * Add product to shopping cart action
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function checkoutCartAddProduct(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem->getId() && !$quoteItem->getParentItem()) {
            $productId = $quoteItem->getProductId();
            $this->_event(Mage_Reports_Model_Event::EVENT_PRODUCT_TO_CART, $productId);
        }
        return $this;
    }

    /**
     * Add product to wishlist action
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function wishlistAddProduct(Varien_Event_Observer $observer)
    {
        return $this->_event(Mage_Reports_Model_Event::EVENT_PRODUCT_TO_WISHLIST,
            $observer->getEvent()->getProduct()->getId()
        );
    }

    /**
     * Share customer wishlist action
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function wishlistShare(Varien_Event_Observer $observer)
    {
        return $this->_event(Mage_Reports_Model_Event::EVENT_WISHLIST_SHARE,
            $observer->getEvent()->getWishlist()->getId()
        );
    }

    /**
     * Clean events by old visitors
     *
     * @see Global Log Clean Settings
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Reports_Model_Event_Observer
     */
    public function eventClean(Varien_Event_Observer $observer)
    {
        /* @var $event Mage_Reports_Model_Event */
        $event = Mage::getModel('Mage_Reports_Model_Event');
        $event->clean();

        Mage::getModel('Mage_Reports_Model_Product_Index_Compared')->clean();
        Mage::getModel('Mage_Reports_Model_Product_Index_Viewed')->clean();

        return $this;
    }
}
