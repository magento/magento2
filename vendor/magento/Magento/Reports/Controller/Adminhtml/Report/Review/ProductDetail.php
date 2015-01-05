<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Review;

class ProductDetail extends \Magento\Reports\Controller\Adminhtml\Report\Review
{
    /**
     * Details action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Review::report_review'
        )->_addBreadcrumb(
            __('Products Report'),
            __('Products Report')
        )->_addBreadcrumb(
            __('Product Reviews'),
            __('Product Reviews')
        )->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Reports\Block\Adminhtml\Review\Detail')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Details'));
        $this->_view->renderLayout();
    }
}
