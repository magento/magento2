<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Controller\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\ProductAlert\Controller\Add as AddController;
use Magento\ProductAlert\Helper\Data as ProductAlertHelper;

/**
 * Customer product alerts listing page (price and stock subscriptions).
 */
class Index extends AddController implements HttpGetActionInterface
{
    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param PageFactory $resultPageFactory
     * @param ProductAlertHelper $productAlertHelper
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        private PageFactory $resultPageFactory,
        private ProductAlertHelper $productAlertHelper
    ) {
        parent::__construct($context, $customerSession);
    }

    /**
     * Display customer product alert subscriptions.
     *
     * @return Page
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->productAlertHelper->isPriceAlertAllowed()
            && !$this->productAlertHelper->isStockAlertAllowed()
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Product Alerts'));
        return $resultPage;
    }
}
