<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

use Magento\Framework\App\ObjectManager;
use Magento\Payment\Block\Transparent\Iframe;

/**
 * Class Redirect
 */
class Redirect extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /**
     * Retrieve params and put javascript into iframe
     *
     * @return void
     */
    public function execute()
    {
        $helper = $this->dataFactory->create('frontend');

        $redirectParams = $this->getRequest()->getParams();
        $params = [];
        if (!empty($redirectParams['success'])
            && isset($redirectParams['x_invoice_num'])
            && isset($redirectParams['controller_action_name'])
        ) {
            $this->_getDirectPostSession()->unsetData('quote_id');
            $params['redirect_parent'] = $helper->getSuccessOrderUrl([]);
        }

        if (!empty($redirectParams['error_msg'])) {
            $cancelOrder = empty($redirectParams['x_invoice_num']);
            $this->_returnCustomerQuote($cancelOrder, $redirectParams['error_msg']);
            $params['error_msg'] = $redirectParams['error_msg'];
        }

        if (isset($redirectParams['controller_action_name'])
            && strpos($redirectParams['controller_action_name'], 'sales_order_') !== false
        ) {
            unset($redirectParams['controller_action_name']);
            unset($params['redirect_parent']);
        }

        $this->_coreRegistry->register(Iframe::REGISTRY_KEY, $params);
        $this->_view->addPageLayoutHandles();
        $this->_view->loadLayout(false)->renderLayout();
    }
}
