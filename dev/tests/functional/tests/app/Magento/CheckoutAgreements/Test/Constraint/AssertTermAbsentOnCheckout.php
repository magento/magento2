<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;

/**
 * Verify Terms and Conditions checkbox is absent on checkout page.
 */
class AssertTermAbsentOnCheckout extends AbstractConstraint
{
    /**
     * Verify Terms and Conditions checkbox is absent on checkout page.
     *
     * @param ObjectManager $objectManager
     * @param $products
     * @param CheckoutOnepage $checkoutOnepage
     * @param $shipping
     * @param $payment
     * @param CheckoutAgreement $agreement
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function processAssert(
        ObjectManager $objectManager,
        $products,
        CheckoutOnepage $checkoutOnepage,
        $shipping,
        $payment,
        CheckoutAgreement $agreement
    ) {
        $shippingAddressData = ['shippingAddress' => ['dataSet' => 'US_address_1']];
        $productsData = ['products' => $products];
        $shippingMethodData = ['shipping' => $shipping];
        $paymentData = ['payment' => $payment];

        $products = $objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            $productsData
        )->run();
        $objectManager->create(\Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class, $products)->run();
        $objectManager->create(
            \Magento\Checkout\Test\TestStep\ProceedToCheckoutStep::class
        )->run();
        $objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingAddressStep::class,
            $shippingAddressData
        )->run();
        $objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            $shippingMethodData
        )->run();
        $objectManager->create(\Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class, $paymentData)->run();

        \PHPUnit_Framework_Assert::assertFalse(
            $checkoutOnepage->getAgreementReview()->checkAgreement($agreement),
            'Checkout Agreement \'' . $agreement->getName() . '\' is present in the Place order step.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Checkout Agreement is absent on checkout page.';
    }
}
