<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Payment;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Checkout payment method block.
 */
class Method extends Block
{
    /**
     * Wait element.
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Place order button selector.
     *
     * @var string
     */
    protected $placeOrderButton = '.actions-toolbar .checkout';

    /**
     * Billing address block selector.
     *
     * @var string
     */
    protected $billingAddressSelector = '.payment-method-billing-address';

    /**
     * PayPal load spinner.
     *
     * @var string
     */
    protected $preloaderSpinner = '#preloaderSpinner';

    /**
     * Continue to PayPal button for Braintree.
     *
     * @var string
     */
    protected $continueToBraintreePaypalButton = '#braintree_paypal_continue_to';

    /**
     * Pay with Paypal button for Braintree.
     *
     * @var string
     */
    protected $payWithBraintreePaypalButton = '#braintree_paypal_pay_with';

    /**
     * Wait for PayPal page is loaded.
     *
     * @return void
     */
    public function waitForFormLoaded()
    {
        $this->waitForElementNotVisible($this->preloaderSpinner);
    }

    /**
     * Place order.
     *
     * @return void
     */
    public function clickPlaceOrder()
    {
        $this->_rootElement->find($this->placeOrderButton)->click();
        $this->waitForElementNotVisible($this->waitElement);
    }

    /**
     * Click Continue to Paypal button.
     *
     * @return string
     */
    public function clickContinueToPaypal()
    {
        $currentWindow = $this->browser->getCurrentWindow();
        $this->waitForElementNotVisible($this->waitElement);
        $this->_rootElement->find($this->continueToBraintreePaypalButton)->click();
        $this->waitForElementNotVisible($this->waitElement);
        return $currentWindow;
    }

    /**
     * Click Pay with Paypal button.
     *
     * @return string
     */
    public function clickPayWithPaypal()
    {
        $currentWindow = $this->browser->getCurrentWindow();
        $this->waitForElementNotVisible($this->waitElement);
        $this->_rootElement->find($this->payWithBraintreePaypalButton)->click();
        $this->waitForElementNotVisible($this->waitElement);
        return $currentWindow;
    }
    
    /**
     * Click "Check out with PayPal" button.
     */
    public function inContextPaypalCheckout()
    {
        $this->_rootElement->find($this->placeOrderButton)->click();
        $this->browser->selectWindow();
        $this->waitForFormLoaded();
        $this->browser->closeWindow();
    }

    /**
     * Get "Billing Address" block.
     *
     * @return \Magento\Checkout\Test\Block\Onepage\Payment\Method\Billing
     */
    public function getBillingBlock()
    {
        $element = $this->_rootElement->find($this->billingAddressSelector);

        return $this->blockFactory->create(
            \Magento\Checkout\Test\Block\Onepage\Payment\Method\Billing::class,
            ['element' => $element]
        );
    }
}
