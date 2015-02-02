<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that correctly display cross-sell section.
 */
class AssertProductCrossSellSection extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that correctly display cross-sell section.
     *
     * @param CheckoutCart $checkoutCart
     * @param InjectableFixture[] $promotedProducts
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, array $promotedProducts)
    {
        $productNames = [];
        $pageProductNames = [];

        foreach ($promotedProducts as $promotedProduct) {
            $productNames[] = $promotedProduct->getName();
        }
        foreach ($checkoutCart->getCrosssellBlock()->getProducts() as $productItem) {
            $pageProductNames[] = $productItem->getProductName();
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $productNames,
            $pageProductNames,
            'Wrong display cross-sell section.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cross-sell section is correctly display.';
    }
}
