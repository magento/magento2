<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * Vault provider.
     *
     * @var array
     */
    protected $vault;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $vault
     */
    public function __construct (CheckoutOnepage $checkoutOnepage, array $vault)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->vault = $vault;
    }

    /**
     * Run step that selects saved credit card.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutOnepage->getPaymentBlock()->selectPaymentMethod($this->vault);
    }
}
