<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Totals extends \Magento\Reports\Controller\Adminhtml\Report\Customer implements HttpGetActionInterface
{
    /**
     * Customers by orders total action
     *
     * @return void
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
