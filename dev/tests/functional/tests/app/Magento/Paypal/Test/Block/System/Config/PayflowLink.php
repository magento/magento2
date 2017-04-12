<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Configuration for Payflow Link payment method.
 */
class PayflowLink extends Block
{
    /**
     * Payflow Link credentials fields sectors array.
     *
     * @var array
     */
    private $fields = [
        'Partner' => '#payment_us_paypal_payment_gateways_payflow_link_us_payflow_link_required_payflow_link_payflow_' .
            'link_partner',
        'Vendor' => '#payment_us_paypal_payment_gateways_payflow_link_us_payflow_link_required_payflow_link_payflow_' .
            'link_vendor',
        'User' => '#payment_us_paypal_payment_gateways_payflow_link_us_payflow_link_required_payflow_link_payflow_' .
            'link_user',
        'Password' => '#payment_us_paypal_payment_gateways_payflow_link_us_payflow_link_required_payflow_link_payflow' .
            '_link_pwd'
    ];

    /**
     * Payflow Link enablers fields sectors array.
     *
     * @var array
     */
    private $enablers = [
        'Enable Payflow Link' => '#payment_us_paypal_payment_gateways_payflow_link_us_payflow_link_required_enable_' .
        'payflow_link',
        'Enable Express Checkout' => '#payment_us_paypal_payment_gateways_payflow_link_us_payflow_link_required_' .
            'enable_express_checkout',
        'Enable PayPal Credit' => '#payment_us_paypal_payment_gateways_payflow_link_us_payflow_link_required_enable_' .
            'express_checkout_bml'
    ];

    /**
     * Payflow Link 'Configure' button selector.
     *
     * @var string
     */
    private $configurePayflowLinkButton = '#payment_us_paypal_payment_gateways_payflow_link_us-head';

    /**
     *  Specify credentials in PayPal Payflow Link configuration.
     *
     * @return void
     */
    public function specifyCredentials()
    {
        $this->_rootElement->find($this->fields['Partner'])->setValue('1');
        $this->_rootElement->find($this->fields['Vendor'])->setValue('1');
        $this->_rootElement->find($this->fields['User'])->setValue('1');
        $this->_rootElement->find($this->fields['Password'])->setValue('1');
    }

    /**
     *  Set fields for credentials empty in PayPal Payflow Link configuration.
     *
     * @return void
     */
    public function clearCredentials()
    {
        $this->_rootElement->find($this->fields['Partner'])->setValue('');
        $this->_rootElement->find($this->fields['Vendor'])->setValue('');
        $this->_rootElement->find($this->fields['User'])->setValue('');
        $this->_rootElement->find($this->fields['Password'])->setValue('');
    }

    /**
     * Return credentials fields selectors.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Return enabler fields selectors.
     *
     * @return array
     */
    public function getEnablerFields()
    {
        return $this->enablers;
    }

    /**
     *  Click 'Configure' button to expand PayPal Payflow Link configuration.
     *
     * @return void
     */
    public function clickConfigureButton()
    {
        $this->_rootElement->find($this->configurePayflowLinkButton)->click();
    }

    /**
     * Set 'Enable this Solution' = Yes.
     *
     * @return void
     */
    public function enablePayflowLink()
    {
        $this->_rootElement->find(
            $this->enablers['Enable Payflow Link'],
            Locator::SELECTOR_CSS,
            'select'
        )->setValue('Yes');
    }

    /**
     * Set 'Enable this Solution' = No.
     *
     * @return void
     */
    public function disablePayflowLink()
    {
        $this->_rootElement->find(
            $this->enablers['Enable Payflow Link'],
            Locator::SELECTOR_CSS,
            'select'
        )->setValue('No');
    }
}
