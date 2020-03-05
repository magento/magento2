<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Controller\Adminhtml\Report\Shopcart;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Shopcart\Customer
 */
class Customer extends \Magento\Reports\Controller\Adminhtml\Report\Shopcart implements HttpGetActionInterface
{
    /**
     * Customer shopping carts action
     *
     * @return void
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
