<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductInCart
 * Assertion that the product is correctly displayed in cart
 */
class AssertProductInCart extends AbstractConstraint
{
    /**
     * Assertion that the product is correctly displayed in cart
     *
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @param BrowserInterface $browser
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        FixtureInterface $product,
        BrowserInterface $browser,
        CheckoutCart $checkoutCart
    ) {
        // Add product to cart
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->fillOptions($product);
        $catalogProductView->getViewBlock()->clickAddToCart();
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();

        // Check price
        $this->assertOnShoppingCart($product, $checkoutCart);
    }

    /**
     * Assert prices on the shopping cart
     *
     * @param FixtureInterface $product
     * @param CheckoutCart $checkoutCart
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function assertOnShoppingCart(FixtureInterface $product, CheckoutCart $checkoutCart)
    {
        $checkoutCart->open();
        /** @var CatalogProductSimple $product */
        $customOptions = $product->getCustomOptions();
        $checkoutData = $product->getCheckoutData();
        $checkoutCartItem = isset($checkoutData['cartItem']) ? $checkoutData['cartItem'] : [];
        $checkoutCustomOptions = isset($checkoutData['options']['custom_options'])
            ? $checkoutData['options']['custom_options']
            : [];
        $fixturePrice = $product->getPrice();
        $specialPrice = $product->getSpecialPrice();
        $cartItem = $checkoutCart->getCartBlock()->getCartItem($product);
        $formPrice = $cartItem->getPrice();

        if ($specialPrice) {
            $fixturePrice = $specialPrice;
        }
        if (isset($checkoutCartItem['price'])) {
            $fixturePrice = $checkoutCartItem['price'];
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
