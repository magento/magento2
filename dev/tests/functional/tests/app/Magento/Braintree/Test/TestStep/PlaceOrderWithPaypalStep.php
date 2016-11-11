<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertGrandTotalOrderReview;
use Magento\Checkout\Test\Constraint\AssertBillingAddressAbsentInPayment;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Customer;
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
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Checkout method.
     *
     * @var string
     */
    protected $checkoutMethod;

    /**
     * Shipping carrier and method.
     *
     * @var array
     */
    protected $shipping;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param AssertGrandTotalOrderReview $assertGrandTotalOrderReview
     * @param AssertBillingAddressAbsentInPayment $assertBillingAddressAbsentInPayment
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param FixtureFactory $fixtureFactory
     * @param Customer $customer
     * @param string $checkoutMethod
     * @param array $products
     * @param array $prices
     * @param array $shipping
     *
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertGrandTotalOrderReview $assertGrandTotalOrderReview,
        AssertBillingAddressAbsentInPayment $assertBillingAddressAbsentInPayment,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        FixtureFactory $fixtureFactory,
        Customer $customer = null,
        $checkoutMethod,

        array $products,
        array $prices = [],
        array $shipping = []

    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->assertGrandTotalOrderReview = $assertGrandTotalOrderReview;
        $this->assertBillingAddressAbsentInPayment = $assertBillingAddressAbsentInPayment;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->fixtureFactory = $fixtureFactory;
        $this->customer = $customer;
        $this->checkoutMethod = $checkoutMethod;
        $this->products = $products;
        $this->prices = $prices;
        $this->shipping = $shipping;
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

        if ($this->checkoutMethod === 'guest' &&
            empty($this->shipping['shipping_method']) &&
            empty($this->shipping['shipping_service'])) {
            $this->checkoutOnepage->getLoginBlock()->fillGuestFields($this->customer);
        }

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
