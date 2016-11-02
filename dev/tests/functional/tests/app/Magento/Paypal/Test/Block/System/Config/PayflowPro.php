<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Configuration for Payflow Pro payment method.
 */
class PayflowPro extends Block
{
    /**
     * Payflow Pro credentials fields sectors array.
     *
     * @var array
     */
    private $fields = [
        'Partner' => '#payment_us_paypal_payment_gateways_paypal_payflowpro_with_express_checkout_paypal_payflow_' .
            'required_paypal_payflow_api_settings_partner',
        'Vendor' => '#payment_us_paypal_payment_gateways_paypal_payflowpro_with_express_checkout_paypal_payflow_' .
            'required_paypal_payflow_api_settings_vendor',
        'User' => '#payment_us_paypal_payment_gateways_paypal_payflowpro_with_express_checkout_paypal_payflow_' .
            'required_paypal_payflow_api_settings_user',
        'Password' => '#payment_us_paypal_payment_gateways_paypal_payflowpro_with_express_checkout_paypal_payflow_' .
            'required_paypal_payflow_api_settings_pwd'
    ];

    /**
     * Payflow Pro enablers fields sectors array.
     *
     * @var array
     */
    private $enablers = [
        'Enable this Solution' => '#payment_us_paypal_payment_gateways_paypal_payflowpro_with_express_checkout_paypal' .
            '_payflow_required_enable_paypal_payflow',
        'Enable PayPal Credit' => '#payment_us_paypal_payment_gateways_paypal_payflowpro_with_express_checkout_paypal' .
            '_payflow_required_enable_express_checkout_bml_payflow',
        'Vault Enabled' => '#payment_us_paypal_payment_gateways_paypal_payflowpro_with_express_checkout_paypal_' .
            'payflow_required_payflowpro_cc_vault_active'
    ];

    /**
     * Payflow Pro 'Configure' button selector.
     *
     * @var string
     */
    private $configureProButton = '#payment_us_paypal_payment_gateways_paypal_payflowpro_with_express_checkout-head';

    /**
     *  Specify credentials in PayPal Payflow Pro configuration.
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
     *  Set fields for credentials empty in PayPal Payflow Pro configuration.
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
     *  Click 'Configure' button to expand PayPal Payflow Pro configuration.
     *
     * @return void
     */
    public function clickConfigureButton()
    {
        $this->_rootElement->find($this->configureProButton)->click();
    }

    /**
     * Set 'Enable this Solution' = Yes.
     *
     * @return void
     */
    public function enablePayflowPro()
    {
        $this->_rootElement->find(
            $this->enablers['Enable this Solution'],
            Locator::SELECTOR_CSS,
            'select'
        )->setValue('Yes');
    }

    /**
     * Set 'Enable this Solution' = No.
     *
     * @return void
     */
    public function disablePayflowPro()
    {
        $this->_rootElement->find(
            $this->enablers['Enable this Solution'],
            Locator::SELECTOR_CSS,
            'select'
        )->setValue('No');
    }
}
