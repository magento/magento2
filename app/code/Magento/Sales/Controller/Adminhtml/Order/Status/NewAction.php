<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

class NewAction extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * New status form
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->_getSession()->getFormData(true);
        if ($data) {
            $status = $this->_objectManager->create('Magento\Sales\Model\Order\Status')->setData($data);
            $this->_coreRegistry->register('current_status', $status);
        }
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::system_order_statuses');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Order Status'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Create New Order Status'));
        $this->_view->renderLayout();
    }
}
