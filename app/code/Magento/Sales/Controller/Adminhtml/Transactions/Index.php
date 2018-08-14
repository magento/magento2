<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Transactions;

use Magento\Backend\Model\View\Result\Page;

class Index extends \Magento\Sales\Controller\Adminhtml\Transactions
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
