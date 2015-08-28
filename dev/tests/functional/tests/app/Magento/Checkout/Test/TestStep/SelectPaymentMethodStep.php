<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Fixture\CreditCard;

/**
 * Class SelectPaymentMethodStep
 * Selecting payment method
 */
class SelectPaymentMethodStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Payment information
     *
     * @var string
     */
    protected $payment;

    /**
     * Credit card information
     *
     * @var string
     */
    protected $creditCard;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $payment
     * @param CreditCard|null $creditCard
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        array $payment,
        CreditCard $creditCard = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->payment = $payment;
        $this->creditCard = $creditCard;
    }

    /**
     * Run step that selecting payment method
     *
     * @return void
     */
    public function run()
    {
        if ($this->payment['method'] !== 'free') {
            $this->checkoutOnepage->getPaymentBlock()->selectPaymentMethod($this->payment, $this->creditCard);
        }
    }
}
