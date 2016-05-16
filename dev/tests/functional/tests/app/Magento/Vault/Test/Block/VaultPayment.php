<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;

class VaultPayment extends Block
{
    /**
     * Credit card selector.
     */
    private $creditCardSelector = './/*[contains(@for, "_vault_item")]/span[text()="%s"]';

    /**
     * Verify if saved credit card is present as a payment option.
     *
     * @param string $creditCard
     * @return bool
     */
    public function isSavedCreditCardPresent($creditCard)
    {
        $paymentLabelSelector = sprintf($this->creditCardSelector, $creditCard);
        return $this->browser->find($paymentLabelSelector, Locator::SELECTOR_XPATH)->isVisible();
    }
}
