<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Product;

class Lowstock extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Reports::lowstock';

    /**
     * Low stock action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_products_lowstock'
        )->_addBreadcrumb(
            __('Low Stock'),
            __('Low Stock')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Low Stock Report'));
        $this->_view->renderLayout();
    }
}
