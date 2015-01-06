<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

class Assign extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * Assign status to state form
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::system_order_statuses');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Order Status'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Assign Order Status to State'));
        $this->_view->renderLayout();
    }
}
