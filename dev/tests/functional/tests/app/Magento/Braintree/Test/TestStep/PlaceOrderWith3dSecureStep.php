<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertGrandTotalOrderReview;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Braintree\Test\Fixture\Secure3dBraintree;

/**
 * Place order with 3D Secure step.
 */
class PlaceOrderWith3dSecureStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * 3D Secure fixture.
     *
     * @var Secure3dBraintree
     */
    private $secure3d;

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
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param AssertGrandTotalOrderReview $assertGrandTotalOrderReview
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param Secure3dBraintree $secure3d
     * @param array $prices
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertGrandTotalOrderReview $assertGrandTotalOrderReview,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        Secure3dBraintree $secure3d,
        array $prices = []
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->secure3d = $secure3d;
        $this->assertGrandTotalOrderReview = $assertGrandTotalOrderReview;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->prices = $prices;
    }

    /**
     * Place order after checking order totals and passing 3D Secure on review step.
     *
     * @return array
     */
    public function run()
    {
        if (isset($this->prices['grandTotal'])) {
            $this->assertGrandTotalOrderReview->processAssert($this->checkoutOnepage, $this->prices['grandTotal']);
        }
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();

        $this->checkoutOnepage->getBraintree3dSecureBlock()->fill($this->secure3d);
        return ['orderId' => $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId()];
    }
}
