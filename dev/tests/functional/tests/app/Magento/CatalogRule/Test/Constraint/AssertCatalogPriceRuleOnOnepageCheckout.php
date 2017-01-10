<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Catalog Price Rule is applied on OnePage Checkout page.
 */
class AssertCatalogPriceRuleOnOnepageCheckout extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that Catalog Price Rule is applied & it impacts on product's discount price on OnePage Checkout page.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param Customer $customer
     * @param array $products
     * @param array $cartPrice
     * @param array $shipping
     * @param array $payment
     * @return void
     */
    public function processAssert(
        CheckoutOnepage $checkoutOnepage,
        Customer $customer,
        array $products,
        array $cartPrice,
        array $shipping,
        array $payment
    ) {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $products]
        )->run();
        $this->objectManager->create(\Magento\Checkout\Test\TestStep\ProceedToCheckoutStep::class)->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillBillingInformationStep::class,
            ['customer' => $customer, 'checkoutMethod' => 'register']
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            ['shipping' => $shipping]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class,
            ['payment' => $payment]
        )->run();
        $actualPrices['grand_total'] = $checkoutOnepage->getReviewBlock()->getGrandTotal();
        $actualPrices['sub_total'] = $checkoutOnepage->getReviewBlock()->getSubtotal();
        $expectedPrices['grand_total'] = $cartPrice['grand_total'];
        $expectedPrices['sub_total'] = $cartPrice['sub_total'];
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedPrices,
            $actualPrices,
            'Wrong total cart prices are displayed.'
            . "\nExpected: " . implode(PHP_EOL, $expectedPrices)
            . "\nActual: " . implode(PHP_EOL, $actualPrices) . "\n"
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed catalog price rule data on OnePage Checkout equals to passed from fixture.';
    }
}
