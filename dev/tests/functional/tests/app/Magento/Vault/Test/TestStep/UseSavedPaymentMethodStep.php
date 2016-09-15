<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Select saved payment.
 */
class UseSavedPaymentMethodStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Vault provider code.
     *
     * @var string
     */
    protected $vaultCode;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $vaultCode
     */
    public function __construct (CheckoutOnepage $checkoutOnepage, string $vaultCode)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->vaultCode = $vaultCode;
    }

    /**
     * Run step that selects saved credit card.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutOnepage->getPaymentBlock()->selectPaymentMethod($this->vaultCode);
    }
}
