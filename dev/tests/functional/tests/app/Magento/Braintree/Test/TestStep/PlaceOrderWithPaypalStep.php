<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertGrandTotalOrderReview;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
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
     * @var CheckoutOnepageSuccess
     */
    private $checkoutOnepageSuccess;

    /**
     * @var array
     */
    private $prices;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param AssertGrandTotalOrderReview $assertGrandTotalOrderReview
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param array $prices
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertGrandTotalOrderReview $assertGrandTotalOrderReview,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        array $prices = []
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->assertGrandTotalOrderReview = $assertGrandTotalOrderReview;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
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
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();
        $this->checkoutOnepage->getBraintreePaypalBlock()->process();
        return ['orderId' => $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId()];
    }
}
