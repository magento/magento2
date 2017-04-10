<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Assert uses per coupon configuration works ok.
 */
class AssertUsesPerCouponWorks extends AbstractConstraint
{
    /**
     * Message when coupon is applied successfully.
     *
     * @var string
     */
    private $successCouponAppliedMessage = 'Your coupon was successfully applied.';

    /**
     * Message after coupon applying failed.
     *
     * @var string
     */
    private $errorCouponAppliedMessage = 'Coupon code is not valid';

    /**
     * First product from precondition.
     *
     * @var CatalogProductSimple
     */
    protected $productForSalesRule1;

    /**
     * Assert uses per coupon configuration works ok.
     *
     * @param SalesRule $salesRule
     * @param CatalogProductSimple $productForSalesRule1
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $shippingAddress
     * @param array $generatedCouponCodes
     * @param array $payment
     * @param array $shipping
     *
     * @return void
     */
    public function processAssert(
        SalesRule $salesRule,
        CatalogProductSimple $productForSalesRule1,
        CheckoutOnepage $checkoutOnepage,
        array $shippingAddress,
        array $generatedCouponCodes,
        array $payment,
        array $shipping
    ) {
        $objectManager = \Magento\Mtf\ObjectManager::getInstance();

        //need to place order one more time than uses_per_coupon to get error message.
        for ($i = 0; $i < $salesRule->getUsesPerCoupon() + 1; $i++) {
            //add product to cart.
            $objectManager->create(
                \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
                ['products' => [$productForSalesRule1]]
            )->run();

            // go to checkout.
            $checkoutOnepage->open();

            //fill shipping address.
            $objectManager->create(
                \Magento\Checkout\Test\TestStep\FillShippingAddressStep::class,
                ['shippingAddress' => $shippingAddress]
            )->run();

            //fill sipping method.
            $objectManager->create(
                \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
                ['shipping' => $shipping]
            )->run();

            // select payment method.
            $objectManager->create(
                \Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class,
                ['payment' => $payment])
                ->run();

            // apply coupon code and get message.
            $message = $checkoutOnepage->getDiscountCodesBlock()->applyCouponCode($generatedCouponCodes[0]['code']);

            // check coupon code applying message.
            $this->assertCouponCodeApplyingMessage($message, $salesRule->getUsesPerCoupon(), $i);

            // place order.
            $objectManager->create(\Magento\Checkout\Test\TestStep\PlaceOrderStep::class)->run();
        }
    }

    /**
     * @param string $message
     * @param int $usesPerCoupon
     * @param int $i
     *
     * @return void
     */
    private function assertCouponCodeApplyingMessage($message, $usesPerCoupon, $i)
    {
        if ($usesPerCoupon > $i) {
            \PHPUnit_Framework_Assert::assertEquals(
                $this->successCouponAppliedMessage,
                $message
            );
        } else {
            \PHPUnit_Framework_Assert::assertEquals(
                $this->errorCouponAppliedMessage,
                $message
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Uses per coupon configuration works ok.';
    }
}
