<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Controller\Report\Product;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Reports\Model\Event;
use Magento\Reports\Model\Product\Index\ViewedFactory;
use Magento\Reports\Model\ReportStatus;
use Magento\Reports\Observer\EventSaver;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Report Action
 */
class View implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ReportStatus $reportStatus
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param Visitor $customerVisitor
     * @param ViewedFactory $productIndexFactory
     * @param EventSaver $eventSaver
     */
    public function __construct(
        private readonly Context $context,
        private readonly JsonFactory $resultJsonFactory,
        private readonly ReportStatus $reportStatus,
        private readonly StoreManagerInterface $storeManager,
        private readonly Session $customerSession,
        private readonly Visitor $customerVisitor,
        private readonly ViewedFactory $productIndexFactory,
        private readonly EventSaver $eventSaver
    ) {
        $this->request = $context->getRequest();
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        if ($this->reportStatus->isReportEnabled((string)Event::EVENT_PRODUCT_VIEW)) {
            $productId = $this->request->getParam('product_id');

            $viewData['product_id'] = $productId;
            $viewData['store_id']   = $this->storeManager->getStore()->getId();
            if ($this->customerSession->isLoggedIn()) {
                $viewData['customer_id'] = $this->customerSession->getCustomerId();
            } else {
                $viewData['visitor_id'] = $this->customerVisitor->getId();
            }
            $this->productIndexFactory->create()->setData($viewData)->save()->calculate();
            $this->eventSaver->save(Event::EVENT_PRODUCT_VIEW, $productId);
        }

        return $result->setData([]);
    }
}
