<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Product;

class Lowstock extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Check is allowed for report
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Reports::lowstock');
    }

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
