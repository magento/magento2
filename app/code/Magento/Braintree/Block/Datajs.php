<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block;

class Datajs extends \Magento\Framework\View\Element\Template
{
    const JS_SRC_CONFIG_PATH        = 'payment/braintree/data_js';
    const MERCHANT_ID_CONFIG_PATH   = 'payment/braintree/merchant_id';

    /**
     * Returns data.js script source from store config
     *
     * @return string
     */
    public function getJsSrc()
    {
        return $this->_scopeConfig->getValue(
            self::JS_SRC_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        );
    }
    
    /**
     * Returns merchant_id from store config
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->_scopeConfig->getValue(
            self::MERCHANT_ID_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        );
    }

    /**
     * Returns the credit card form id
     *
     * @return string
     */
    public function getFormId()
    {
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();

        if ($controllerName == 'creditcard') {
            switch($actionName) {
                case 'newcard':
                    return 'form-validate';
                case 'edit':
                    return 'form-validate';
                case 'delete':
                    return 'delete-form';
            }
        } elseif ($controllerName == 'multishipping') {
            return 'multishipping-billing-form';
        } elseif ($controllerName == 'order_create') {
            return 'edit_form';
        } else {
            return 'co-payment-form';
        }
    }
}
