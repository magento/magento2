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
     * Save credit card check box.
     *
     * @var string
     */
    protected $vaultCheckbox = '#%s_vault_enabler';

    /**
     * PayPal load spinner.
     *
     * @var string
     */
    protected $preloaderSpinner = '#preloaderSpinner';

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
        $this->_rootElement->find($this->placeOrderButton)->click();
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
            '\Magento\Checkout\Test\Block\Onepage\Payment\Method\Billing',
            ['element' => $element]
        );
    }

    /**
     * Save credit card.
     *
     * @param string $paymentMethod
     * @param string $creditCardSave
     * @return void
     */
    public function saveCreditCard($paymentMethod, $creditCardSave)
    {
        $saveCard = sprintf($this->vaultCheckbox, $paymentMethod);
        $this->_rootElement->find($saveCard, Locator::SELECTOR_CSS, 'checkbox')->setValue($creditCardSave);
    }
}
