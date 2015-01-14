<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

class Edit extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * Editing existing status form
     *
     * @return void
     */
    public function execute()
    {
        $status = $this->_initStatus();
        if ($status) {
            $this->_coreRegistry->register('current_status', $status);
            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::system_order_statuses');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Order Status'));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Order Status'));
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addError(__('We can\'t find this order status.'));
            $this->_redirect('sales/');
        }
    }
}
