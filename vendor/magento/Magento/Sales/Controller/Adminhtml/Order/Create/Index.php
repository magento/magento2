<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
