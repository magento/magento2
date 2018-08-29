<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertConfigurableProductInCart
 * Assertion that the product is correctly displayed in cart
 */
class AssertConfigurableProductInCart extends AbstractConstraint
{
    /**
     * Assertion that the product is correctly displayed in cart
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param ConfigurableProduct $product
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        ConfigurableProduct $product
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $catalogProductView->getViewBlock()->addToCart($product);
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();
        $checkoutCart->open();

        $checkoutData = $product->getCheckoutData();
        $price = $checkoutCart->getCartBlock()->getCartItem($product)->getPrice();
        \PHPUnit\Framework\Assert::assertEquals(
            $checkoutData['cartItem']['price'],
            $price,
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
        return 'Product price in shopping cart is correct.';
    }
}
