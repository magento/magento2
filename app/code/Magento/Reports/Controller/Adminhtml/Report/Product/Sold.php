<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Product;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Sold extends \Magento\Reports\Controller\Adminhtml\Report\Product implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Reports::sold';

    /**
     * Sold Products Report Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_products_sold'
        )->_addBreadcrumb(
            __('Products Ordered'),
            __('Products Ordered')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Ordered Products Report'));
        $this->_view->renderLayout();
    }
}
