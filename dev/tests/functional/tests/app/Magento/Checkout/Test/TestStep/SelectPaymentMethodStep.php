<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Fixture\CreditCard;

/**
 * Select payment method step.
 */
class SelectPaymentMethodStep implements TestStepInterface
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
     * @var string
     */
    protected $payment;

    /**
     * Credit card information.
     *
     * @var string
     */
    protected $creditCard;

    /**
     * If fill credit card data should be filled on 3rd party side.
     *
     * @var bool
     */
    private $fillCreditCardOn3rdParty;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $payment
     * @param CreditCard|null $creditCard
     * @param bool $fillCreditCardOn3rdParty
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        array $payment,
        CreditCard $creditCard = null,
        $fillCreditCardOn3rdParty = false
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->payment = $payment;
        $this->creditCard = $creditCard;
        $this->fillCreditCardOn3rdParty = $fillCreditCardOn3rdParty;
    }

    /**
     * Run step that selecting payment method.
     *
     * @return void
     */
    public function run()
    {
        if ($this->payment['method'] !== 'free') {
            $this->checkoutOnepage->getPaymentBlock()->selectPaymentMethod(
                $this->payment,
                $this->creditCard,
                $this->fillCreditCardOn3rdParty
            );
        }
    }
}
