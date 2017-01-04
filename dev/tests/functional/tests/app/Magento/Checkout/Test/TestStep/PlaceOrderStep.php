<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertGrandTotalOrderReview;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Place order in one page checkout.
 */
class PlaceOrderStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Assert that Order Grand Total is correct on checkout page review block.
     *
     * @var AssertGrandTotalOrderReview
     */
    private $assertGrandTotalOrderReview;

    /**
     * One page checkout success page.
     *
     * @var CheckoutOnepageSuccess
     */
    private $checkoutOnepageSuccess;

    /**
     * Price array.
     *
     * @var array
     */
    private $prices;

    /**
     * Factory for fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Array of product entities.
     *
     * @var array
     */
    private $products;

    /**
     * Fixture OrderInjectable.
     *
     * @var OrderInjectable
     */
    private $order;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param AssertGrandTotalOrderReview $assertGrandTotalOrderReview
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param FixtureFactory $fixtureFactory
     * @param OrderInjectable|null $order
     * @param array|null $products
     * @param array|null $prices
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertGrandTotalOrderReview $assertGrandTotalOrderReview,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        FixtureFactory $fixtureFactory,
        OrderInjectable $order = null,
        array $products = [],
        array $prices = []
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->assertGrandTotalOrderReview = $assertGrandTotalOrderReview;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->fixtureFactory = $fixtureFactory;
        $this->order = $order;
        $this->products = $products;
        $this->prices = $prices;
    }

    /**
     * Place order after checking order totals on review step.
     *
     * @return array
     */
    public function run()
    {
        if (isset($this->prices['grandTotal'])) {
            $this->assertGrandTotalOrderReview->processAssert($this->checkoutOnepage, $this->prices['grandTotal']);
        }
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();
        $orderId = $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId();
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
