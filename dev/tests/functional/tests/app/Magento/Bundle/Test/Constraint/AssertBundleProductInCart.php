<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductInCart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assertion that bundle product is correctly displayed in cart.
 */
class AssertBundleProductInCart extends AssertProductInCart
{
    /**
     * Count prices.
     *
     * @param FixtureInterface $product
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    protected function countPrices(FixtureInterface $product, CheckoutCart $checkoutCart)
    {
        parent::countPrices($product, $checkoutCart);
        $this->countSubItemPrice($product);
    }

    /**
     * Count subItem price.
     *
     * @param FixtureInterface $product
     * @return void
     */
    private function countSubItemPrice(FixtureInterface $product)
    {
        $checkoutData = $product->getCheckoutData();
        if (isset($checkoutData['cartItem']['subItemPrice'])) {
            $this->fixtureActualPrice += $checkoutData["cartItem"]["subItemPrice"];
        }
    }
}
