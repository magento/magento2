<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Payment;

use Magento\Mtf\Block\Form;

/**
 * Class Methods
 * One page checkout status payment method block
 *
 */
class Methods extends Form
{
    /**
     * Payment method input selector
     *
     * @var string
     */
    protected $paymentMethodInput = '#p_method_%s';

    /**
     * Labels for payment methods
     *
     * @var string
     */
    protected $paymentMethodLabels = '[for^=p_method_]';

    /**
     * Label for payment methods
     *
     * @var string
     */
    protected $paymentMethodLabel = '[for=p_method_%s]';

    /**
     * Continue checkout button
     *
     * @var string
     */
    protected $continue = '#payment-buttons-container button';

    /**
     * Wait element
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Purchase order number selector
     *
     * @var string
     */
    protected $purchaseOrderNumber = '#po_number';

    /**
     * Select payment method
     *
     * @param array $payment
     * @throws \Exception
     * @return void
     */
    public function selectPaymentMethod(array $payment)
    {
        $paymentSelector = $this->_rootElement->find(sprintf($this->paymentMethodInput, $payment['method']));
        if ($paymentSelector->isVisible()) {
            $paymentSelector->click();
        } else {
            $paymentCount = count($this->_rootElement->getElements($this->paymentMethodLabels));
            $paymentSelector = $this->_rootElement->find(sprintf($this->paymentMethodLabel, $payment['method']));
            if ($paymentCount !== 1 && !$paymentSelector->isVisible()) {
                throw new \Exception('Such payment method is absent.');
            }
        }
        if ($payment['method'] == "purchaseorder") {
            $this->_rootElement->find($this->purchaseOrderNumber)->setValue($payment['po_number']);
        }
        if (isset($payment['dataConfig']['payment_form_class'])) {
            $paymentFormClass = $payment['dataConfig']['payment_form_class'];
            /** @var \Magento\Payment\Test\Block\Form\Cc $formBlock */
            $formBlock = $this->blockFactory->create(
                $paymentFormClass,
                ['element' => $this->_rootElement->find('#payment_form_' . $payment['method'])]
            );
            $formBlock->fill($payment['credit_card']);
        }
    }

    /**
     * Press "Continue" button
     *
     * @return void
     */
    public function clickContinue()
    {
        $this->_rootElement->find($this->continue)->click();
        $browser = $this->browser;
        $selector = $this->waitElement;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() == false ? true : null;
            }
        );
    }
}
