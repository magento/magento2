<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 * This assert adds product to cart and checks price.
 */
class AssertProductTierPriceInCart extends AbstractConstraint
{
    /**
     * Price on form.
     *
     * @var string
     */
    private $formPrice;

    /**
     * Fixture actual price.
     *
     * @var string
     */
    private $fixtureActualPrice;

    /**
     * Fixture price.
     *
     * @var string
     */
    private $fixturePrice;

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
        $checkoutCart->open();
        $checkoutCart->getCartBlock()->clearShoppingCart();
        // Add product to cart
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $requiredQty = $product->getDataFieldConfig('tier_price')['source']->getData()[0]['price_qty'];
        $catalogProductView->getViewBlock()->setQtyAndClickAddToCart($requiredQty);
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
    private function countPrices(FixtureInterface $product, CheckoutCart $checkoutCart)
    {
        /** @var CatalogProductSimple $product */
        $this->fixturePrice = $product->getPrice();
        $this->prepareFormPrice($product, $checkoutCart);
        $this->countCheckoutCartItemPrice($product);
    }

    /**
     * Prepare form price.
     *
     * @param FixtureInterface $product
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    private function prepareFormPrice(FixtureInterface $product, CheckoutCart $checkoutCart)
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
    private function countCheckoutCartItemPrice(FixtureInterface $product)
    {
        $tierPrice = $product->getDataFieldConfig('tier_price')['source']->getData()[0];

        if ($tierPrice['value_type'] === "Discount") {
            $this->fixtureActualPrice = $this->fixturePrice * (1 - $tierPrice['percentage_value'] / 100);
        } else {
            $this->fixtureActualPrice = $tierPrice['price'];
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
