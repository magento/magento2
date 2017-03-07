<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Block;
use Magento\Payment\Test\Fixture\CreditCard;

/**
 * Checkout payment block.
 */
class Payment extends Block
{
    /**
     * Payment method input selector.
     *
     * @var string
     */
    protected $paymentMethodInput = '[id*="%s"]';

    /**
     * Labels for payment methods.
     *
     * @var string
     */
    protected $paymentMethodLabels = '.payment-method:not([style="display: none;"]) .payment-method-title label';

    /**
     * Label for payment methods.
     *
     * @var string
     */
    protected $paymentMethodLabel = '[for*="%s"]';

    /**
     * Continue checkout button.
     *
     * @var string
     */
    protected $continue = '#payment-buttons-container button';

    /**
     * Place order button.
     *
     * @var string
     */
    protected $placeOrder = '.payment-method._active .action.primary.checkout';

    /**
     * Wait element.
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Purchase order number selector.
     *
     * @var string
     */
    protected $purchaseOrderNumber = '#po_number';

    /**
     * Selector for active payment method.
     *
     * @var string
     */
    protected $activePaymentMethodSelector = '.payment-method._active';

    /**
     * Select payment method.
     *
     * @param array $payment
     * @param CreditCard|null $creditCard
     * @param bool $fillCreditCardOn3rdParty
     * @throws \Exception
     * @return void
     */
    public function selectPaymentMethod(
        array $payment,
        CreditCard $creditCard = null,
        $fillCreditCardOn3rdParty = false
    ) {
        $paymentMethod = $payment['method'];
        $paymentSelector = sprintf($this->paymentMethodInput, $paymentMethod);
        $paymentLabelSelector = sprintf($this->paymentMethodLabel, $paymentMethod);

        try {
            $this->waitForElementNotVisible($this->waitElement);
            $this->waitForElementVisible($paymentLabelSelector);
        } catch (\Exception $exception) {
            throw new \Exception('Such payment method is absent.');
        }
        $browser = $this->browser;
        $browser->waitUntil(
            function () use ($browser, $paymentSelector) {
                return $browser->find($paymentSelector);
            }
        );
        $paymentRadioButton = $this->_rootElement->find($paymentSelector);
        if ($paymentRadioButton->isVisible()) {
            $paymentRadioButton->click();
        }

        if ($paymentMethod == "purchaseorder") {
            $this->_rootElement->find($this->purchaseOrderNumber)->setValue($payment['po_number']);
        }
        if ($creditCard !== null && $fillCreditCardOn3rdParty === false) {
            $module = $creditCard->hasData('payment_code') ? ucfirst($creditCard->getPaymentCode()) : 'Payment';
            /** @var \Magento\Payment\Test\Block\Form\PaymentCc $formBlock */
            $formBlock = $this->blockFactory->create(
                "\\Magento\\{$module}\\Test\\Block\\Form\\{$module}Cc",
                ['element' => $this->_rootElement->find('#payment_form_' . $paymentMethod)]
            );
            $formBlock->fill($creditCard);
        }
    }

    /**
     * Check visibility of payment method block by payment method type.
     *
     * @param array $payment
     * @return bool
     */
    public function isVisiblePaymentMethod(array $payment)
    {
        $paymentSelector = sprintf($this->paymentMethodInput, $payment['method']);

        return $this->_rootElement->find($paymentSelector)->isVisible();
    }

    /**
     * Get selected payment method block.
     *
     * @return \Magento\Checkout\Test\Block\Onepage\Payment\Method
     */
    public function getSelectedPaymentMethodBlock()
    {
        $element = $this->_rootElement->find($this->activePaymentMethodSelector);

        return $this->blockFactory->create(
            \Magento\Checkout\Test\Block\Onepage\Payment\Method::class,
            ['element' => $element]
        );
    }

    /**
     * Press "Place Order" button.
     *
     * @return void
     */
    public function placeOrder()
    {
        $this->_rootElement->find($this->placeOrder)->click();
        $this->waitForElementNotVisible($this->waitElement);
    }
}
