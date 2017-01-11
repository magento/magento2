<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Catalog Price Rule is not applied in Shopping Cart.
 */
class AssertCatalogPriceRuleNotAppliedShoppingCart extends AbstractConstraint
{
    /**
     * Assert that Catalog Price Rule is not applied for product(s) in Shopping Cart.
     *
     * @param CheckoutCart $checkoutCartPage
     * @param array $products
     * @param array $productPrice
     * @return void
     */
    public function processAssert(
        CheckoutCart $checkoutCartPage,
        array $products,
        array $productPrice
    ) {
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $products]
        )->run();
        $checkoutCartPage->open();
        foreach ($products as $key => $product) {
            $actualPrice = $checkoutCartPage->getCartBlock()->getCartItem($product)->getSubtotalPrice();
            \PHPUnit_Framework_Assert::assertEquals(
                $productPrice[$key]['regular'],
                $actualPrice,
                'Wrong product price is displayed.'
                . "\nExpected: " . $productPrice[$key]['regular']
                . "\nActual: " . $actualPrice . "\n"
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
        return 'Displayed catalog price rule data in shopping cart(frontend) equals to passed from fixture.';
    }
}
