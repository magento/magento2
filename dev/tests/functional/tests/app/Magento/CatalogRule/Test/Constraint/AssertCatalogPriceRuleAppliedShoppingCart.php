<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Assert that Catalog Price Rule is applied in Shopping Cart.
 */
class AssertCatalogPriceRuleAppliedShoppingCart extends AbstractConstraint
{
    /**
     * Assert that Catalog Price Rule is applied for product(s) in Shopping Cart
     * according to Priority(Priority/Stop Further Rules Processing).
     *
     * @param CheckoutCart $checkoutCartPage
     * @param array $products
     * @param array $cartPrice
     * @param array $productPrice
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CheckoutCart $checkoutCartPage,
        array $products,
        array $cartPrice,
        array $productPrice,
        Customer $customer = null
    ) {
        if ($customer !== null) {
            $this->objectManager->create(
                '\Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
                ['customer' => $customer]
            )->run();
        } else {
            $this->objectManager->create('\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep')->run();
        }

        $this->objectManager->create(
            '\Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => $products]
        )->run();
        $checkoutCartPage->open();
        foreach ($products as $key => $product) {
            $actualPrice = $checkoutCartPage->getCartBlock()->getCartItem($product)->getSubtotalPrice();
            \PHPUnit_Framework_Assert::assertEquals(
                $productPrice[$key]['sub_total'],
                $actualPrice,
                'Wrong product price is displayed.'
                . "\nExpected: " . $productPrice[$key]['sub_total']
                . "\nActual: " . $actualPrice . "\n"
            );
        }
        $checkoutCartPage->getTotalsBlock()->waitForShippingPriceBlock();
        $actualPrices['sub_total'] = $checkoutCartPage->getTotalsBlock()->getSubtotal();
        $actualPrices['grand_total'] = $checkoutCartPage->getTotalsBlock()->getGrandTotal();
        $expectedPrices['sub_total'] = $cartPrice['sub_total'];
        $expectedPrices['grand_total'] = $cartPrice['grand_total'];
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedPrices,
            $actualPrices,
            'Wrong total cart prices are displayed.'
        );
    }

    /**
     * Text of catalog price rule visibility in Shopping Cart (frontend).
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed catalog price rule data in shopping cart(frontend) equals to passed from fixture.';
    }
}
