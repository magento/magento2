<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Create\Cancel
 *
 * @since 2.0.0
 */
class Cancel extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Cancel order create
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
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
