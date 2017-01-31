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
class AssertCatalogPriceRuleAppliedOnepageCheckout extends AbstractConstraint
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
            '\Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
        $this->objectManager->create(
            '\Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => $products]
        )->run();
        $this->objectManager->create('\Magento\Checkout\Test\TestStep\ProceedToCheckoutStep')->run();
        $this->objectManager->create(
            '\Magento\Checkout\Test\TestStep\FillBillingInformationStep',
            ['customer' => $customer, 'checkoutMethod' => 'register']
        )->run();
        $this->objectManager->create(
            '\Magento\Checkout\Test\TestStep\FillShippingMethodStep',
            ['shipping' => $shipping]
        )->run();
        $this->objectManager->create(
            '\Magento\Checkout\Test\TestStep\SelectPaymentMethodStep',
            ['payment' => $payment]
        )->run();
        $actualPrices['grand_total'] = $checkoutOnepage->getReviewBlock()->getGrandTotal();
        $actualPrices['sub_total'] = $checkoutOnepage->getReviewBlock()->getSubtotal();
        $expectedPrices['grand_total'] = $cartPrice['grand_total'] + $cartPrice['shipping_price'];
        $expectedPrices['sub_total'] = $cartPrice['sub_total'];
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedPrices,
            $actualPrices,
            'Wrong total cart prices are displayed.'
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
