<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Review;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Review\Product
 *
 */
class Product extends \Magento\Reports\Controller\Adminhtml\Report\Review
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
