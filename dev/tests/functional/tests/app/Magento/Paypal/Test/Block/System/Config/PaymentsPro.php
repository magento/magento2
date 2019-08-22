<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Configuration for Payments Pro payment method.
 */
class PaymentsPro extends Block
{
    /**
     * Payments Pro enablers fields sectors array.
     *
     * @var array
     */
    private $fields = [
        'Partner' => '#payment_us_paypal_group_all_in_one_wpp_usuk_paypal_payflow_required_paypal_payflow_api_' .
            'settings_partner',
        'Vendor' => '#payment_us_paypal_group_all_in_one_wpp_usuk_paypal_payflow_required_paypal_payflow_api_' .
            'settings_vendor',
        'User' => '#payment_us_paypal_group_all_in_one_wpp_usuk_paypal_payflow_required_paypal_payflow_api_settings' .
            '_user',
        'Password' => '#payment_us_paypal_group_all_in_one_wpp_usuk_paypal_payflow_required_paypal_payflow_api_' .
            'settings_pwd'
    ];

    /**
     * Payments Pro enablers fields sectors array.
     *
     * @var array
     */
    private $enablers = [
        'Enable this Solution' => '#payment_us_paypal_group_all_in_one_wpp_usuk_paypal_payflow_required_enable_paypal' .
            '_payflow',
        'Enable PayPal Credit' => '#payment_us_paypal_group_all_in_one_wpp_usuk_paypal_payflow_required_enable_' .
            'express_checkout_bml_payflow',
        'Vault Enabled' => '#payment_us_paypal_group_all_in_one_wpp_usuk_paypal_payflow_required_payflowpro_cc_vault' .
            '_active'
    ];

    /**
     * Payments Pro 'Configure' button selector.
     *
     * @var string
     */
    private $configureProButton = '#payment_us_paypal_group_all_in_one_wpp_usuk-head';

    /**
     *  Specify credentials in PayPal Payments Pro configuration.
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
     *  Set fields for credentials empty in PayPal Payments Pro configuration.
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
     *  Click 'Configure' button to expand PayPal Payments Pro configuration.
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
    public function enablePaymentsPro()
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
    public function disablePaymentsPro()
    {
        $this->_rootElement->find(
            $this->enablers['Enable this Solution'],
            Locator::SELECTOR_CSS,
            'select'
        )->setValue('No');
    }
}
