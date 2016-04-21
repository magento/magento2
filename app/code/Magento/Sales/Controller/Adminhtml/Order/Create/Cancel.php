<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

class Cancel extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Cancel order create
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($orderId = $this->_getSession()->getReordered()) {
            $this->_getSession()->clearStorage();
            $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        } else {
            $this->_getSession()->clearStorage();
            $resultRedirect->setPath('sales/*');
        }
        return $resultRedirect;
    }
}
