<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Fixture\FixtureFactory;
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
     * @param FixtureFactory $fixtureFactory
     * @param string $creditCardClass
     * @param array|CreditCard|null $creditCard
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        array $payment,
        FixtureFactory $fixtureFactory,
        $creditCardClass = 'credit_card',
        $creditCard = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->payment = $payment;
        if (isset($creditCard['dataset'])) {
            $this->creditCard = $fixtureFactory->createByCode($creditCardClass, ['dataset' => $creditCard['dataset']]);
        }
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
