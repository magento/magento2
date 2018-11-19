<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Review;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Customer extends \Magento\Reports\Controller\Adminhtml\Report\Review implements HttpGetActionInterface
{
    /**
     * Customer Reviews Report action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Review::report_review_customer'
        )->_addBreadcrumb(
            __('Customers Report'),
            __('Customers Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Customer Reviews Report'));
        $this->_view->renderLayout();
    }
}
