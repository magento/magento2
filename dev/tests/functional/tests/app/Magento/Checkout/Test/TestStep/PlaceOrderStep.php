<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertGrandTotalOrderReview;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\TestStep\TestStepInterface;

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
    protected $checkoutOnepage;

    /**
     * Assert that Order Grand Total is correct on checkout page review block.
     *
     * @var AssertGrandTotalOrderReview
     */
    protected $assertGrandTotalOrderReview;

    /**
     * One page checkout success page.
     *
     * @var CheckoutOnepageSuccess
     */
    protected $checkoutOnepageSuccess;

    /**
     * Price array.
     *
     * @var array
     */
    protected $prices;

    /**
     * @construct
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
        $this->prices = $prices;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
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

        return ['orderId' => $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId()];
    }
}
