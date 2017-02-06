<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Fixture\CreditCard;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Place order using PayPal Payments Pro Hosted Solution during one page checkout.
 */
class PlaceOrderWithHostedProStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Products fixtures.
     *
     * @var FixtureInterface[]
     */
    private $products;

    /**
     * Payment information.
     *
     * @var string
     */
    private $payment;

    /**
     * Credit card information.
     *
     * @var string
     */
    private $creditCard;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param FixtureFactory $fixtureFactory
     * @param CreditCard $creditCard
     * @param array $payment
     * @param array $products
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        FixtureFactory $fixtureFactory,
        CreditCard $creditCard,
        array $payment,
        array $products
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->fixtureFactory = $fixtureFactory;
        $this->creditCard = $creditCard;
        $this->payment = $payment;
        $this->products = $products;
    }

    /**
     * Place order with Hosted Pro.
     *
     * @return array
     */
    public function run()
    {
        $attempts = 1;
        $this->checkoutOnepage->getPaymentBlock()->selectPaymentMethod($this->payment);
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();
        $this->checkoutOnepage->getHostedProBlock()->fillPaymentData($this->creditCard);
        // As Paypal Sandbox is not stable there are three attempts given to place order
        while ($this->checkoutOnepage->getHostedProBlock()->isErrorMessageVisible() && $attempts <= 3) {
            $this->checkoutOnepage->getHostedProBlock()->fillPaymentData($this->creditCard);
            $attempts++;
        }
        /** @var OrderInjectable $order */
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'data' => [
                    'entity_id' => ['products' => $this->products]
                ]
            ]
        );
        return ['order' => $order];
    }
}
