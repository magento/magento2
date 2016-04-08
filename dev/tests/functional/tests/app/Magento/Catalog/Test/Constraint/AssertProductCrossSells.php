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
 * Assert that product is displayed in cross-sell section.
 */
class AssertProductCrossSells extends AbstractConstraint
{
    /**
     * Assert that product is displayed in cross-sell section.
     *
     * @param BrowserInterface $browser
     * @param CheckoutCart $checkoutCart
     * @param CatalogProductSimple $product
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface[]|null $promotedProducts
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CheckoutCart $checkoutCart,
        CatalogProductSimple $product,
        CatalogProductView $catalogProductView,
        array $promotedProducts = null
    ) {
        $errors = [];
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
            if (!$checkoutCart->getCrosssellBlock()->getProductItem($promotedProduct)->isVisible()) {
                $errors[] = 'Product \'' . $promotedProduct->getName() . '\' is absent in cross-sell section.';
            }
        }

        \PHPUnit_Framework_Assert::assertEmpty($errors, implode(" ", $errors));
    }

    /**
     * Text success product is displayed in cross-sell section.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is displayed in cross-sell section.';
    }
}
