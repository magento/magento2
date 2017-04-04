<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Fixture\CreditCard;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Place order using PayPal Payflow Link Solution during one page checkout.
 */
class PlaceOrderWithPayflowLinkStep implements TestStepInterface
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
     * @var array
     */
    private $products;

    /**
     * Payment information.
     *
     * @var array
     */
    private $payment;

    /**
     * Credit card information.
     *
     * @var CreditCard
     */
    private $creditCard;

    /**
     * Order fixture.
     *
     * @var OrderInjectable
     */
    private $order;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param FixtureFactory $fixtureFactory
     * @param CreditCard $creditCard
     * @param array $payment
     * @param array $products
     * @param OrderInjectable|null $order
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        FixtureFactory $fixtureFactory,
        CreditCard $creditCard,
        array $payment,
        array $products,
        OrderInjectable $order = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->fixtureFactory = $fixtureFactory;
        $this->creditCard = $creditCard;
        $this->payment = $payment;
        $this->products = $products;
        $this->order = $order;
    }

    /**
     * Place order with Payflow Link.
     *
     * @return array
     */
    public function run()
    {
        $this->checkoutOnepage->getPaymentBlock()->selectPaymentMethod($this->payment);
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();
        $this->checkoutOnepage->getPayflowLinkBlock()->fillPaymentData($this->creditCard);

        $data = [];
        if ($this->order !== null) {
            $data = $this->order->getData();
        }

        /** @var OrderInjectable $order */
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'data' => array_replace_recursive(
                    $data,
                    ['entity_id' => ['products' => $this->products]]
                )
            ]
        );

        return ['order' => $order];
    }
}
