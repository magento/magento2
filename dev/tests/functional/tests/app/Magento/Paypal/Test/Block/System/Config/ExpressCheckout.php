<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Configuration for Express Checkout payment method.
 */
class ExpressCheckout extends Block
{
    /**
     * @var string
     */
    private $fields = [
        'Email Associated with PayPal Merchant Account' => '#payment_us_paypal_alternative_payment_methods_express_' .
            'checkout_us_express_checkout_required_express_checkout_required_express_checkout_business_account',
        'API Username' => '#payment_us_paypal_alternative_payment_methods_express_checkout_us_express_checkout_' .
            'required_express_checkout_required_express_checkout_api_username',
        'API Password' => '#payment_us_paypal_alternative_payment_methods_express_checkout_us_express_checkout_' .
            'required_express_checkout_required_express_checkout_api_password',
        'API Signature' => '#payment_us_paypal_alternative_payment_methods_express_checkout_us_express_checkout_' .
            'required_express_checkout_required_express_checkout_api_signature',
        'Merchant Account ID' => '#payment_us_paypal_alternative_payment_methods_express_checkout_us_express_' .
            'checkout_required_merchant_id',
        'Sort Order PayPal Credit' => '#payment_us_paypal_alternative_payment_methods_express_checkout_us_express_' .
            'checkout_required_express_checkout_bml_sort_order',
    ];

    /**
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @var array
     */
    private $enablers = [
        'Enable this Solution' => '#payment_us_paypal_alternative_payment_methods_express_checkout_us_express_' .
        'checkout_required_enable_express_checkout',
        'Enable In-Context Checkout Experience' => '#payment_us_paypal_alternative_payment_methods_express_checkout_' .
            'us_express_checkout_required_enable_in_context_checkout',
        'Enable PayPal Credit' => '#payment_us_paypal_alternative_payment_methods_express_checkout_us_express_' .
            'checkout_required_enable_express_checkout_bml'
    ];

    /**
     * @var string
     */
    private $configureExpressButton = '#payment_us_paypal_alternative_payment_methods_express_checkout_us-head';

    /**
     *  Specify credentials in PayPal Express Checkout configuration.
     */
    public function specifyCredentials()
    {
        $this->_rootElement->find($this->fields['Email Associated with PayPal Merchant Account'])
            ->setValue('test@test.com');
        $this->_rootElement->find($this->fields['API Username'])->setValue('1');
        $this->_rootElement->find($this->fields['API Password'])->setValue('1');
        $this->_rootElement->find($this->fields['API Signature'])->setValue('1');
    }

    /**
     *  Set fields for credentials empty in PayPal Express Checkout configuration.
     */
    public function clearCredentials()
    {
        $this->_rootElement->find($this->fields['Email Associated with PayPal Merchant Account'])->setValue('');
        $this->_rootElement->find($this->fields['API Username'])->setValue('');
        $this->_rootElement->find($this->fields['API Password'])->setValue('');
        $this->_rootElement->find($this->fields['API Signature'])->setValue('');
    }

    /**
     *  Specify Merchant Account ID in PayPal Express Checkout configuration.
     */
    public function specifyMerchantAccountId()
    {
        $this->_rootElement->find($this->fields['Merchant Account ID'])->setValue('1');
    }

    /**
     * @return array
     */
    public function getEnablerFields()
    {
        return $this->enablers;
    }

    /**
     *  Click 'Configure' button to expand PayPal Express Checkout configuration.
     */
    public function clickConfigureButton()
    {
        $this->_rootElement->find($this->configureExpressButton)->click();
    }

    /**
     * Set 'Enable this Solution' = Yes.
     */
    public function enableExpressCheckout()
    {
        $this->_rootElement->find(
            $this->enablers['Enable this Solution'],
            Locator::SELECTOR_CSS,
            'select'
        )->setValue('Yes');
    }

    /**
     * Set 'Enable this Solution' = No.
     */
    public function disableExpressCheckout()
    {
        $this->_rootElement->find(
            $this->enablers['Enable this Solution'],
            Locator::SELECTOR_CSS,
            'select'
        )->setValue('No');
    }
}
