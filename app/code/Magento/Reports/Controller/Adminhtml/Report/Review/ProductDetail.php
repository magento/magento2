<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Review;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Review\ProductDetail
 *
 * @since 2.0.0
 */
class ProductDetail extends \Magento\Reports\Controller\Adminhtml\Report\Review
{
    /**
     * Details action
     *
     * @return void
     * @since 2.0.0
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
            $this->_view->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Review\Detail::class)
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Details'));
        $this->_view->renderLayout();
    }
}
