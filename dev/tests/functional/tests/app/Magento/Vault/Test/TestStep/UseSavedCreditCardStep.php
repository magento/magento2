<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Select saved credit card.
 */
class UseSavedCreditCardStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Payment information.
     *
     * @var array
     */
    protected $payment;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $payment
     */
    public function __construct (CheckoutOnepage $checkoutOnepage, array $payment)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->payment = $payment;
    }

    /**
     * Run step that selects saved credit card.
     *
     * @return void
     */
    public function run()
    {
        $this->payment['method'] .= '_item_';
        $this->checkoutOnepage->getPaymentBlock()->selectPaymentMethod($this->payment);
    }
}
