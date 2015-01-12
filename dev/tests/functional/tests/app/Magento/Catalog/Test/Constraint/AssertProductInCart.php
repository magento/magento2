<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductInCart
 * Assertion that the product is correctly displayed in cart
 */
class AssertProductInCart extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assertion that the product is correctly displayed in cart
     *
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @param Browser $browser
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        FixtureInterface $product,
        Browser $browser,
        CheckoutCart $checkoutCart
    ) {
        // Add product to cart
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->fillOptions($product);
        $catalogProductView->getViewBlock()->clickAddToCart();

        // Check price
        $this->assertOnShoppingCart($product, $checkoutCart);
    }

    /**
     * Assert prices on the shopping cart
     *
     * @param FixtureInterface $product
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    protected function assertOnShoppingCart(FixtureInterface $product, CheckoutCart $checkoutCart)
    {
        /** @var CatalogProductSimple $product */
        $customOptions = $product->getCustomOptions();
        $checkoutData = $product->getCheckoutData();
        $checkoutCustomOptions = isset($checkoutData['options']['custom_options'])
            ? $checkoutData['options']['custom_options']
            : [];
        $fixturePrice = $product->getPrice();
        $groupPrice = $product->getGroupPrice();
        $specialPrice = $product->getSpecialPrice();
        $cartItem = $checkoutCart->getCartBlock()->getCartItem($product);
        $formPrice = $cartItem->getPrice();

        if ($groupPrice) {
            $groupPrice = reset($groupPrice);
            $fixturePrice = $groupPrice['price'];
        }
        if ($specialPrice) {
            $fixturePrice = $specialPrice;
        }
        $fixtureActualPrice = $fixturePrice;

        foreach ($checkoutCustomOptions as $checkoutOption) {
            $attributeKey = str_replace('attribute_key_', '', $checkoutOption['title']);
            $optionKey = str_replace('option_key_', '', $checkoutOption['value']);
            $option = $customOptions[$attributeKey]['options'][$optionKey];

            if ('Fixed' == $option['price_type']) {
                $fixtureActualPrice += $option['price'];
            } else {
                $fixtureActualPrice += ($fixturePrice / 100) * $option['price'];
            }
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureActualPrice,
            $formPrice,
            'Product price in shopping cart is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is correctly displayed in cart.';
    }
}
