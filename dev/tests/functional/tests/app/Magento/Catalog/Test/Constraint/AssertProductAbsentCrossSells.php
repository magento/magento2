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
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that product is not displayed in cross-sell section.
 */
class AssertProductAbsentCrossSells extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that product is not displayed in cross-sell section.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductSimple $product
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param InjectableFixture[]|null $promotedProducts
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductSimple $product,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        array $promotedProducts = null
    ) {
        if (!$promotedProducts) {
            $promotedProducts = $product->hasData('cross_sell_products')
                ? $product->getDataFieldConfig('cross_sell_products')['source']->getProducts()
                : [];
        }

        $checkoutCart->open();
        $checkoutCart->getCartBlock()->clearShoppingCart();

        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->addToCart($product);
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();
        $checkoutCart->open();
        foreach ($promotedProducts as $promotedProduct) {
            \PHPUnit_Framework_Assert::assertFalse(
                $checkoutCart->getCrosssellBlock()->getProductItem($promotedProduct)->isVisible(),
                'Product \'' . $promotedProduct->getName() . '\' exists in cross-sell section.'
            );
        }
    }

    /**
     * Text success product is not displayed in cross-sell section.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is not displayed in cross-sell section.';
    }
}
