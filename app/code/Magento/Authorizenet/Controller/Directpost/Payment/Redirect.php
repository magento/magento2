<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

use Magento\Payment\Block\Transparent\Iframe;

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
        }

        if (isset($redirectParams['controller_action_name'])
            && strpos($redirectParams['controller_action_name'], 'sales_order_') !== false
        ) {
            unset($redirectParams['controller_action_name']);
            unset($params['redirect_parent']);
        }

        $this->_coreRegistry->register(Iframe::REGISTRY_KEY, array_merge($params, $redirectParams));
        $this->_view->addPageLayoutHandles();
        $this->_view->loadLayout(false)->renderLayout();
    }
}
