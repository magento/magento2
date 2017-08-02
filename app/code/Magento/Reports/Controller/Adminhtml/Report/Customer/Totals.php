<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Customer;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Customer\Totals
 *
 * @since 2.0.0
 */
class Totals extends \Magento\Reports\Controller\Adminhtml\Report\Customer
{
    /**
     * Customers by orders total action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_customers_totals'
        )->_addBreadcrumb(
            __('Customers by Orders Total'),
            __('Customers by Orders Total')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Order Total Report'));
        $this->_view->renderLayout();
    }
}
