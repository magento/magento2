<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Block\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Configuration for Braintree payment method.
 */
class Braintree extends Block
{
    /**
     * @var string
     */
    private $fields = [
        'Merchant ID' => '#payment_us_braintree_section_braintree_braintree_required_merchant_id',
        'Public Key' => '#payment_us_braintree_section_braintree_braintree_required_public_key',
        'Private Key' => '#payment_us_braintree_section_braintree_braintree_required_private_key',
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
        'Enable this Solution' => "#payment_us_braintree_section_braintree_active",
        'Enable PayPal through Braintree' => '#payment_us_braintree_section_braintree_active_braintree_paypal',
        'Vault enabled' => '#payment_us_braintree_section_braintree_braintree_cc_vault_active'
    ];

    /**
     * @var string
     */
    private $configureBraintreeButton = '#payment_us_braintree_section_braintree-head';

    /**
     *  Specify credentials in Braintree configuration.
     */
    public function specifyCredentials()
    {
        $this->_rootElement->find($this->fields['Merchant ID'])->setValue('1');
        $this->_rootElement->find($this->fields['Public Key'])->setValue('1');
        $this->_rootElement->find($this->fields['Private Key'])->setValue('1');
    }

    /**
     *  Clear credentials in Braintree configuration.
     */
    public function clearCredentials()
    {
        $this->_rootElement->find($this->fields['Merchant ID'])->setValue('');
        $this->_rootElement->find($this->fields['Public Key'])->setValue('');
        $this->_rootElement->find($this->fields['Private Key'])->setValue(' ');
    }

    /**
     * @return array
     */
    public function getEnablerFields()
    {
        return $this->enablers;
    }

    /**
     *  Click 'Configure' button to expand Braintree configuration.
     */
    public function clickConfigureButton()
    {
        $this->_rootElement->find($this->configureBraintreeButton)->click();
    }

    /**
     * Set 'Enable this Solution' = Yes.
     */
    public function enableBraintree()
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
    public function disableBraintree()
    {
        $this->_rootElement->find(
            $this->enablers['Enable this Solution'],
            Locator::SELECTOR_CSS,
            'select'
        )->setValue('No');
    }
}
