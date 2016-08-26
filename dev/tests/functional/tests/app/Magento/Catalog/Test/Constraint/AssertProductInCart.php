<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
 * Assertion that the product is correctly displayed in cart.
 */
class AssertProductInCart extends AbstractConstraint
{
    /**
     * Price on form.
     *
     * @var string
     */
    protected $formPrice;

    /**
     * Fixture actual price.
     *
     * @var string
     */
    protected $fixtureActualPrice;

    /**
     * Fixture price.
     *
     * @var string
     */
    protected $fixturePrice;

    /**
     * Assertion that the product is correctly displayed in cart.
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
        $this->countPrices($product, $checkoutCart);
        \PHPUnit_Framework_Assert::assertEquals(
            $this->fixtureActualPrice,
            $this->formPrice,
            'Product price in shopping cart is not correct.'
        );
    }

    /**
     * Count prices.
     *
     * @param FixtureInterface $product
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    protected function countPrices(FixtureInterface $product, CheckoutCart $checkoutCart)
    {
        /** @var CatalogProductSimple $product */
        $this->fixturePrice = $product->getPrice();
        $this->prepareFormPrice($product, $checkoutCart);
        $this->countSpecialPrice($product);
        $this->countCheckoutCartItemPrice($product);
        $this->countCustomOptionsPrice($product);
    }

    /**
     * Count count special price.
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function countSpecialPrice(FixtureInterface $product)
    {
        /** @var CatalogProductSimple $product */
        $specialPrice = $product->getSpecialPrice();
        if ($specialPrice) {
            $this->fixturePrice = $product->getSpecialPrice();
        }
    }

    /**
     * Prepare form price.
     *
     * @param FixtureInterface $product
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    protected function prepareFormPrice(FixtureInterface $product, CheckoutCart $checkoutCart)
    {
        $checkoutCart->open();
        $cartItem = $checkoutCart->getCartBlock()->getCartItem($product);
        $this->formPrice = $cartItem->getPrice();
    }

    /**
     * Count cart item price.
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function countCheckoutCartItemPrice(FixtureInterface $product)
    {
        /** @var CatalogProductSimple $product */
        $checkoutData = $product->getCheckoutData();
        $checkoutCartItem = isset($checkoutData['cartItem']) ? $checkoutData['cartItem'] : [];
        if (isset($checkoutCartItem['price'])) {
            $this->fixturePrice = $checkoutCartItem['price'];
        }
        $this->fixtureActualPrice = $this->fixturePrice;
    }

    /**
     * Count custom options price.
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function countCustomOptionsPrice(FixtureInterface $product)
    {
        /** @var CatalogProductSimple $product */
        $customOptions = $product->getCustomOptions();
        $checkoutData = $product->getCheckoutData();
        $checkoutCustomOptions = isset($checkoutData['options']['custom_options'])
            ? $checkoutData['options']['custom_options']
            : [];
        foreach ($checkoutCustomOptions as $checkoutOption) {
            $attributeKey = str_replace('attribute_key_', '', $checkoutOption['title']);
            $optionKey = str_replace('option_key_', '', $checkoutOption['value']);
            $option = $customOptions[$attributeKey]['options'][$optionKey];

            if ('Fixed' == $option['price_type']) {
                $this->fixtureActualPrice += $option['price'];
            } else {
                $this->fixtureActualPrice += ($this->fixturePrice / 100) * $option['price'];
            }
        }
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
