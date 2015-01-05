<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Review;

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
