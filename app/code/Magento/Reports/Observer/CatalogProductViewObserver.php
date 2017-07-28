<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Reports Event observer model
 * @since 2.0.0
 */
class CatalogProductViewObserver implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Reports\Model\Product\Index\ViewedFactory
     * @since 2.0.0
     */
    protected $_productIndxFactory;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Visitor
     * @since 2.0.0
     */
    protected $_customerVisitor;

    /**
     * @var EventSaver
     * @since 2.0.0
     */
    protected $eventSaver;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Reports\Model\Product\Index\ViewedFactory $productIndxFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param EventSaver $eventSaver
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Reports\Model\Product\Index\ViewedFactory $productIndxFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        EventSaver $eventSaver
    ) {
        $this->_storeManager = $storeManager;
        $this->_productIndxFactory = $productIndxFactory;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
        $this->eventSaver = $eventSaver;
    }

    /**
     * View Catalog Product action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productId = $observer->getEvent()->getProduct()->getId();

        $viewData['product_id'] = $productId;
        $viewData['store_id']   = $this->_storeManager->getStore()->getId();
        if ($this->_customerSession->isLoggedIn()) {
            $viewData['customer_id'] = $this->_customerSession->getCustomerId();
        } else {
            $viewData['visitor_id'] = $this->_customerVisitor->getId();
        }

        $this->_productIndxFactory->create()->setData($viewData)->save()->calculate();

        $this->eventSaver->save(\Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW, $productId);
    }
}
