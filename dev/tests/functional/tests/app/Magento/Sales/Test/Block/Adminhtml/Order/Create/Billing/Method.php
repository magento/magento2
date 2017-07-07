<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Payment\Test\Fixture\CreditCard;

/**
 * Adminhtml sales order create payment method block.
 */
class Method extends Block
{
    /**
     * Payment method.
     *
     * @var string
     */
    private $paymentMethod = '#p_method_%s';

    /**
     * Purchase order number selector.
     *
     * @var string
     */
    private $purchaseOrderNumber = '#po_number';

    /**
     * Magento loader selector.
     *
     * @var string
     */
    private $loader = '[data-role=loader]';

    /**
     * Field with Mage error.
     *
     * @var string
     */
    private $mageErrorField = './/*[contains(@name, "payment[")]/following-sibling::label[@class="mage-error"]';

    /**
     * Error label preceding field of credit card form.
     *
     * @var string
     */
    private $errorLabelPrecedingField = './preceding-sibling::*[1][contains(@name, "payment")]';

    /**
     * Select payment method.
     *
     * @param array $payment
     * @param CreditCard|null $creditCard
     * @return void
     */
    public function selectPaymentMethod(array $payment, CreditCard $creditCard = null)
    {
        $paymentMethod = $payment['method'];
        $paymentInput = $this->_rootElement->find(sprintf($this->paymentMethod, $paymentMethod));
        if ($paymentInput->isVisible()) {
            $paymentInput->click();
            $this->waitForElementNotVisible($this->loader);
        }
        if (isset($payment['po_number'])) {
            $this->_rootElement->find($this->purchaseOrderNumber)->setValue($payment['po_number']);
        }
        if ($creditCard !== null) {
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
     * @return array
     */
    public function getJsErrors()
    {
        $data = [];
        $elements = $this->_rootElement->getElements($this->mageErrorField, Locator::SELECTOR_XPATH);
        foreach ($elements as $error) {
            if ($error->isVisible()) {
                $label = $error->find($this->errorLabelPrecedingField, Locator::SELECTOR_XPATH);
                $label = $label->getAttribute('name');
                $label = preg_replace('/payment\[(.*)\]/u', '$1', $label);
                $data[$label] = $error->getText();
            }
        }
        return $data;
    }
}
