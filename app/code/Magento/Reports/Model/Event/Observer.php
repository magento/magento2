<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\Event;

/**
 * Reports Event observer model
 */
class Observer
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Reports\Model\EventFactory
     */
    protected $_eventFactory;

    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory
     */
    protected $_productCompFactory;

    /**
     * @var \Magento\Reports\Model\Product\Index\ViewedFactory
     */
    protected $_productIndxFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Reports\Model\EventFactory $event
     * @param \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory
     * @param \Magento\Reports\Model\Product\Index\ViewedFactory $productIndxFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Reports\Model\EventFactory $event,
        \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory,
        \Magento\Reports\Model\Product\Index\ViewedFactory $productIndxFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor
    ) {
        $this->_storeManager = $storeManager;
        $this->_eventFactory = $event;
        $this->_productCompFactory = $productCompFactory;
        $this->_productIndxFactory = $productIndxFactory;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
    }

    /**
     * Abstract Event observer logic
     *
     * Save event
     *
     * @param int $eventTypeId
     * @param int $objectId
     * @param int $subjectId
     * @param int $subtype
     * @return $this
     */
    protected function _event($eventTypeId, $objectId, $subjectId = null, $subtype = 0)
    {
        if (is_null($subjectId)) {
            if ($this->_customerSession->isLoggedIn()) {
                $subjectId = $this->_customerSession->getCustomerId();
            } else {
                $subjectId = $this->_customerVisitor->getId();
                $subtype = 1;
            }
        }

        /** @var \Magento\Reports\Model\Event $eventModel */
        $eventModel = $this->_eventFactory->create();
        $storeId = $this->_storeManager->getStore()->getId();
        $eventModel->setData([
            'event_type_id' => $eventTypeId,
            'object_id' => $objectId,
            'subject_id' => $subjectId,
            'subtype' => $subtype,
            'store_id' => $storeId,
        ]);

        $eventModel->save();

        return $this;
    }

    /**
     * Customer login action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function customerLogin(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return $this;
        }

        $visitorId = $this->_customerVisitor->getId();
        $customerId = $this->_customerSession->getCustomerId();
        $eventModel = $this->_eventFactory->create();
        $eventModel->updateCustomerType($visitorId, $customerId);

        $this->_productCompFactory->create()->updateCustomerFromVisitor()->calculate();
        $this->_productIndxFactory->create()->updateCustomerFromVisitor()->calculate();

        return $this;
    }

    /**
     * Customer logout processing
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function customerLogout(\Magento\Framework\Event\Observer $observer)
    {
        $this->_productCompFactory->create()->purgeVisitorByCustomer()->calculate();
        $this->_productIndxFactory->create()->purgeVisitorByCustomer()->calculate();
        return $this;
    }

    /**
     * View Catalog Product action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return Observer
     */
    public function catalogProductView(\Magento\Framework\Event\Observer $observer)
    {
        $productId = $observer->getEvent()->getProduct()->getId();

        $viewData['product_id'] = $productId;

        if ($this->_customerSession->isLoggedIn()) {
            $viewData['customer_id'] = $this->_customerSession->getCustomerId();
        } else {
            $viewData['visitor_id'] = $this->_customerVisitor->getId();
        }

        $this->_productIndxFactory->create()->setData($viewData)->save()->calculate();

        return $this->_event(\Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW, $productId);
    }

    /**
     * Send Product link to friends action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return Observer
     */
    public function sendfriendProduct(\Magento\Framework\Event\Observer $observer)
    {
        return $this->_event(
            \Magento\Reports\Model\Event::EVENT_PRODUCT_SEND,
            $observer->getEvent()->getProduct()->getId()
        );
    }

    /**
     * Remove Product from Compare Products action
     *
     * Reset count of compared products cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function catalogProductCompareRemoveProduct(\Magento\Framework\Event\Observer $observer)
    {
        $this->_productCompFactory->create()->calculate();

        return $this;
    }

    /**
     * Remove All Products from Compare Products
     *
     * Reset count of compared products cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function catalogProductCompareClear(\Magento\Framework\Event\Observer $observer)
    {
        $this->_productCompFactory->create()->calculate();

        return $this;
    }

    /**
     * Add Product to Compare Products List action
     *
     * Reset count of compared products cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return Observer
     */
    public function catalogProductCompareAddProduct(\Magento\Framework\Event\Observer $observer)
    {
        $productId = $observer->getEvent()->getProduct()->getId();
        $viewData = ['product_id' => $productId];
        if ($this->_customerSession->isLoggedIn()) {
            $viewData['customer_id'] = $this->_customerSession->getCustomerId();
        } else {
            $viewData['visitor_id'] = $this->_customerVisitor->getId();
        }
        $this->_productCompFactory->create()->setData($viewData)->save()->calculate();
        return $this->_event(\Magento\Reports\Model\Event::EVENT_PRODUCT_COMPARE, $productId);
    }

    /**
     * Add product to shopping cart action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function checkoutCartAddProduct(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem->getId() && !$quoteItem->getParentItem()) {
            $productId = $quoteItem->getProductId();
            $this->_event(\Magento\Reports\Model\Event::EVENT_PRODUCT_TO_CART, $productId);
        }
        return $this;
    }

    /**
     * Add product to wishlist action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return Observer
     */
    public function wishlistAddProduct(\Magento\Framework\Event\Observer $observer)
    {
        return $this->_event(
            \Magento\Reports\Model\Event::EVENT_PRODUCT_TO_WISHLIST,
            $observer->getEvent()->getProduct()->getId()
        );
    }

    /**
     * Share customer wishlist action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return Observer
     */
    public function wishlistShare(\Magento\Framework\Event\Observer $observer)
    {
        return $this->_event(
            \Magento\Reports\Model\Event::EVENT_WISHLIST_SHARE,
            $observer->getEvent()->getWishlist()->getId()
        );
    }
}
