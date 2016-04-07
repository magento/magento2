<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Reports Event observer model
 */
class CatalogProductCompareAddProductObserver implements ObserverInterface
{
    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory
     */
    protected $_productCompFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * @var EventSaver
     */
    protected $eventSaver;

    /**
     * @param \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param EventSaver $eventSaver
     */
    public function __construct(
        \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        EventSaver $eventSaver
    ) {
        $this->_productCompFactory = $productCompFactory;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
        $this->eventSaver = $eventSaver;
    }

    /**
     * Add Product to Compare Products List action
     *
     * Reset count of compared products cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productId = $observer->getEvent()->getProduct()->getId();
        $viewData = ['product_id' => $productId];
        if ($this->_customerSession->isLoggedIn()) {
            $viewData['customer_id'] = $this->_customerSession->getCustomerId();
        } else {
            $viewData['visitor_id'] = $this->_customerVisitor->getId();
        }
        $this->_productCompFactory->create()->setData($viewData)->save()->calculate();

        $this->eventSaver->save(\Magento\Reports\Model\Event::EVENT_PRODUCT_COMPARE, $productId);
    }
}
