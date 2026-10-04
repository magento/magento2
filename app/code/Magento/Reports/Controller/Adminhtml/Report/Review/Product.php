<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Review;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Product extends \Magento\Reports\Controller\Adminhtml\Report\Review implements HttpGetActionInterface
{
    /**
     * Product reviews report action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Review::report_review_product'
        )->_addBreadcrumb(
            __('Products Report'),
            __('Products Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Product Reviews Report'));
        $this->_view->renderLayout();
    }
}
