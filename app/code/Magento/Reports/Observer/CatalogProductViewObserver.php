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
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CatalogProductViewObserver implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

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
     * @var EventSaver
     */
    protected $eventSaver;

    /**
     * @var \Magento\Reports\Model\ReportStatus
     */
    private $reportStatus;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Reports\Model\Product\Index\ViewedFactory $productIndxFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param EventSaver $eventSaver
     * @param \Magento\Reports\Model\ReportStatus $reportStatus
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Reports\Model\Product\Index\ViewedFactory $productIndxFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        EventSaver $eventSaver,
        \Magento\Reports\Model\ReportStatus $reportStatus
    ) {
        $this->_storeManager = $storeManager;
        $this->_productIndxFactory = $productIndxFactory;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
        $this->eventSaver = $eventSaver;
        $this->reportStatus = $reportStatus;
    }

    /**
     * View Catalog Product action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->reportStatus->isReportEnabled(Event::EVENT_PRODUCT_VIEW)) {
            return;
        }

        $productId = $observer->getEvent()->getProduct()->getId();

        $viewData['product_id'] = $productId;
        $viewData['store_id']   = $this->_storeManager->getStore()->getId();
        if ($this->_customerSession->isLoggedIn()) {
            $viewData['customer_id'] = $this->_customerSession->getCustomerId();
        } else {
            $viewData['visitor_id'] = $this->_customerVisitor->getId();
        }

        $this->_productIndxFactory->create()->setData($viewData)->save()->calculate();

        $this->eventSaver->save(Event::EVENT_PRODUCT_VIEW, $productId);
    }
}
