<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Paypal\Test\Fixture\CreditCardHostedPro;
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
     * Onepage checkout success page.
     *
     * @var CheckoutOnepageSuccess
     */
    private $checkoutOnepageSuccess;

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
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param FixtureFactory $fixtureFactory
     * @param CreditCardHostedPro $creditCard
     * @param array $payment
     * @param array $products
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        FixtureFactory $fixtureFactory,
        CreditCardHostedPro $creditCard,
        array $payment,
        array $products
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
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
        $this->checkoutOnepage->getPaymentBlock()->selectPaymentMethod($this->payment);
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();
        $this->checkoutOnepage->getHostedProBlock()->fillPaymentData($this->creditCard);
        /** @var OrderInjectable $order */
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'data' => [
                    'entity_id' => ['products' => $this->products],
                    'orderId' => $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId()
                ]
            ]
        );
        return [
            'orderId' => $order->getId(),
            'order' => $order
        ];
    }
}
