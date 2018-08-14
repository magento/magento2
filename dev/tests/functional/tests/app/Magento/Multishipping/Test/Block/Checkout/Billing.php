<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Multishipping billing information
 */
class Billing extends Form
{
    /**
     * Continue checkout button
     *
     * @var string
     */
    protected $continue = '#payment-continue';

    /**
     * Select payment method
     *
     * @param array $payment
     * @return void
     */
    public function selectPaymentMethod(array $payment)
    {
        $this->_rootElement->find('#p_method_' . $payment['method'], Locator::SELECTOR_CSS)->click();
        if (isset($payment['dataConfig']['payment_form_class'])) {
            $paymentFormClass = $payment['dataConfig']['payment_form_class'];
            /** @var $formBlock \Magento\Mtf\Block\Form */
            $formBlock = $this->blockFactory->create(
                $paymentFormClass,
                ['element' => $this->_rootElement->find('#payment_form_' . $payment['method'], Locator::SELECTOR_CSS)]
            );
            $formBlock->fill($payment['credit_card']);
        }

        $this->_rootElement->find($this->continue, Locator::SELECTOR_CSS)->click();
        $this->waitForElementNotVisible('.please-wait');
    }
}
