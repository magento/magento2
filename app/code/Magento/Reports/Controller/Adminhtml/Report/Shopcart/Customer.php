<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Shopcart;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Shopcart\Customer
 *
 * @since 2.0.0
 */
class Customer extends \Magento\Reports\Controller\Adminhtml\Report\Shopcart
{
    /**
     * Customer shopping carts action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_shopcart_customer'
        )->_addBreadcrumb(
            __('Customers Report'),
            __('Customers Report')
        )->_addContent(
            $this->_view->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Shopcart\Customer::class)
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Customer Shopping Carts'));
        $this->_view->renderLayout();
    }
}
