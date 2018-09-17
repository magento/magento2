<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertGrandTotalOrderReview;
use Magento\Checkout\Test\Constraint\AssertBillingAddressAbsentInPayment;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class PlaceOrderWithPaypalStep
 */
class PlaceOrderWithPaypalStep implements TestStepInterface
{
    /**
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * @var AssertGrandTotalOrderReview
     */
    private $assertGrandTotalOrderReview;

    /**
     * @var AssertBillingAddressAbsentInPayment
     */
    private $assertBillingAddressAbsentInPayment;

    /**
     * @var CheckoutOnepageSuccess
     */
    private $checkoutOnepageSuccess;

    /**
     * @var array
     */
    private $prices;

    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var array
     */
    private $products;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param AssertGrandTotalOrderReview $assertGrandTotalOrderReview
     * @param AssertBillingAddressAbsentInPayment $assertBillingAddressAbsentInPayment
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     * @param array $prices
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertGrandTotalOrderReview $assertGrandTotalOrderReview,
        AssertBillingAddressAbsentInPayment $assertBillingAddressAbsentInPayment,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        FixtureFactory $fixtureFactory,
        array $products,
        array $prices = []
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->assertGrandTotalOrderReview = $assertGrandTotalOrderReview;
        $this->assertBillingAddressAbsentInPayment = $assertBillingAddressAbsentInPayment;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->fixtureFactory = $fixtureFactory;
        $this->products = $products;
        $this->prices = $prices;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (isset($this->prices['grandTotal'])) {
            $this->assertGrandTotalOrderReview->processAssert($this->checkoutOnepage, $this->prices['grandTotal']);
        }

        $this->assertBillingAddressAbsentInPayment->processAssert($this->checkoutOnepage);

        $parentWindow = $this->checkoutOnepage->getPaymentBlock()
            ->getSelectedPaymentMethodBlock()
            ->clickPayWithPaypal();
        $this->checkoutOnepage->getBraintreePaypalBlock()->process($parentWindow);
        
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'data' => [
                    'entity_id' => ['products' => $this->products]
                ]
            ]
        );
        return [
            'orderId' => $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId(),
            'order' => $order
        ];
    }
}
