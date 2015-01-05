<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Customer;

class Orders extends \Magento\Reports\Controller\Adminhtml\Report\Customer
{
    /**
     * Customers by number of orders action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_customers_orders'
        )->_addBreadcrumb(
            __('Customers by Number of Orders'),
            __('Customers by Number of Orders')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Order Count Report'));
        $this->_view->renderLayout();
    }
}
