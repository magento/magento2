<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Test\Block\Onepage\Payment\Method;

use Magento\Mtf\Client\Locator;
use Magento\Checkout\Test\Block\Onepage\Payment\Method;

/**
 * Checkout payment method vault block.
 */
class Vault extends Method
{
    /**
     * Credit card selector.
     *
     * @var string
     */
    private $creditCardSelector = './/*[contains(@for, "_vault_item")]/span[text()="%s"]';

    /**
     * Save credit card check box.
     *
     * @var string
     */
    protected $vaultCheckbox = '#%s_enable_vault';

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

    /**
     * Check if Save credit card check box is visible.
     *
     * @param string $paymentMethod
     * @return bool
     */
    public function isVaultVisible($paymentMethod)
    {
        $saveCard = sprintf($this->vaultCheckbox, $paymentMethod);
        return $this->_rootElement->find($saveCard, Locator::SELECTOR_CSS, 'checkbox')->isVisible();
    }

    /**
     * Verify if saved credit card is present as a payment option.
     *
     * @param string $creditCard
     * @return bool
     */
    public function isSavedCreditCardPresent($creditCard)
    {
        $paymentLabelSelector = sprintf($this->creditCardSelector, $creditCard);
        return $this->_rootElement->find($paymentLabelSelector, Locator::SELECTOR_XPATH)->isVisible();
    }
}
