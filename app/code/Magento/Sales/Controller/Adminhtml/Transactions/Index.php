<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Adminhtml\Transactions;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\Model\View\Result\Page;

class Index extends \Magento\Sales\Controller\Adminhtml\Transactions implements HttpGetActionInterface
{
    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales_transactions');
        $resultPage->getConfig()->getTitle()->prepend(__('Transactions'));

        return $resultPage;
    }
}
