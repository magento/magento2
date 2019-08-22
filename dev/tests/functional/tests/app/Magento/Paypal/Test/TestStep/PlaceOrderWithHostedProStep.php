<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
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
     * @var CheckoutOnepageSuccess
     */
    private $checkoutOnepageSuccess;

    /**
     * Fixture OrderInjectable.
     *
     * @var OrderInjectable
     */
    private $order;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param FixtureFactory $fixtureFactory
     * @param CreditCard $creditCard
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param array $payment
     * @param array $products
     * @param OrderInjectable|null $order
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        FixtureFactory $fixtureFactory,
        CreditCard $creditCard,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        array $payment,
        array $products,
        OrderInjectable $order = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->fixtureFactory = $fixtureFactory;
        $this->creditCard = $creditCard;
        $this->payment = $payment;
        $this->products = $products;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->order = $order;
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

        $orderId = $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId();
        /** @var OrderInjectable $order */
        $data = [
            'id' => $orderId,
            'entity_id' => ['products' => $this->products]
        ];
        $orderData = $this->order !== null ? $this->order->getData() : [];
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            ['data' => array_merge($data, $orderData)]
        );

        return [
            'orderId' => $orderId,
            'order' => $order,
        ];
    }
}
