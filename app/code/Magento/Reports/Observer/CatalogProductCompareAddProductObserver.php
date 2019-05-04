<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Reports\Model\Event;

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
     * @var \Magento\Reports\Model\ReportStatus
     */
    private $reportStatus;

    /**
     * @param \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param EventSaver $eventSaver
     * @param \Magento\Reports\Model\ReportStatus $reportStatus
     */
    public function __construct(
        \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        EventSaver $eventSaver,
        \Magento\Reports\Model\ReportStatus $reportStatus
    ) {
        $this->_productCompFactory = $productCompFactory;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
        $this->eventSaver = $eventSaver;
        $this->reportStatus = $reportStatus;
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
        if (!$this->reportStatus->isReportEnabled(Event::EVENT_PRODUCT_COMPARE)) {
            return;
        }
        $productId = $observer->getEvent()->getProduct()->getId();
        $viewData = ['product_id' => $productId];
        if ($this->_customerSession->isLoggedIn()) {
            $viewData['customer_id'] = $this->_customerSession->getCustomerId();
        } else {
            $viewData['visitor_id'] = $this->_customerVisitor->getId();
        }
        $this->_productCompFactory->create()->setData($viewData)->save()->calculate();

        $this->eventSaver->save(Event::EVENT_PRODUCT_COMPARE, $productId);
    }
}
