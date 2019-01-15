<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Sales\Controller\Adminhtml\Order\Create implements HttpGetActionInterface
{
    /**
     * Index page
     *
     * @return void
     */
    public function execute()
    {
        $this->_initSession();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales_order');
        $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Order'));
        return $resultPage;
    }
}
