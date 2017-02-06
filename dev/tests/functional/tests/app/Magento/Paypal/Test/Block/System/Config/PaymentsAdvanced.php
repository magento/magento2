<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Configuration for Payments Advanced payment method.
 */
class PaymentsAdvanced extends Block
{
    /**
     * Payments Advanced credentials fields sectors array.
     *
     * @var array
     */
    private $fields = [
        'Partner' => '#payment_us_paypal_group_all_in_one_payflow_advanced_required_settings_payments_advanced_partner',
        'Vendor' => '#payment_us_paypal_group_all_in_one_payflow_advanced_required_settings_payments_advanced_vendor',
        'User' => '#payment_us_paypal_group_all_in_one_payflow_advanced_required_settings_payments_advanced_user',
        'Password' => '#payment_us_paypal_group_all_in_one_payflow_advanced_required_settings_payments_advanced_pwd'
    ];

    /**
     * Payments Advanced enablers fields sectors array.
     *
     * @var array
     */
    private $enablers = [
        'Enable this Solution' => '#payment_us_paypal_group_all_in_one_payflow_advanced_required_settings_enable_' .
        'payflow_advanced',
        'Enable PayPal Credit' => '#payment_us_paypal_group_all_in_one_payflow_advanced_required_settings_enable_' .
            'express_checkout_bml'
    ];

    /**
     * Payments Advanced 'Configure' button selector.
     *
     * @var string
     */
    private $configureAdvancedButton = '#payment_us_paypal_group_all_in_one_payflow_advanced-head';

    /**
     *  Specify credentials in PayPal Payments Advanced configuration.
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
     *  Set fields for credentials empty in PayPal Payments Advanced configuration.
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
     *  Click 'Configure' button to expand PayPal Payments Advanced configuration.
     *
     * @return void
     */
    public function clickConfigureButton()
    {
        $this->_rootElement->find($this->configureAdvancedButton)->click();
    }

    /**
     * Set 'Enable this Solution' = Yes.
     *
     * @return void
     */
    public function enablePaymentsAdvanced()
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
    public function disablePaymentsAdvanced()
    {
        $this->_rootElement->find(
            $this->enablers['Enable this Solution'],
            Locator::SELECTOR_CSS,
            'select'
        )->setValue('No');
    }
}
