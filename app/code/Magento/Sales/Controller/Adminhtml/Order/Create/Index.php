<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;


class Index extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Index page
     *
     * @return void
     */
    public function execute()
    {
        $this->_initSession();
        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Sales::sales_order');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Orders'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Order'));
        $this->_view->renderLayout();
    }
}
