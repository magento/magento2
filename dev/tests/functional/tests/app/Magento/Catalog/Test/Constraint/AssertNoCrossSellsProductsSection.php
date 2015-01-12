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
use Mtf\Fixture\InjectableFixture;

/**
 * Class AssertNoCrossSellsProductsSection
 * Assert that product is not displayed in cross-sell section
 */
class AssertNoCrossSellsProductsSection extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that product is not displayed in cross-sell section
     *
     * @param Browser $browser
     * @param CatalogProductSimple $product
     * @param InjectableFixture[] $relatedProducts
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function processAssert(
        Browser $browser,
        CatalogProductSimple $product,
        array $relatedProducts,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart
    ) {
        $checkoutCart->open();
        $checkoutCart->getCartBlock()->clearShoppingCart();

        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->addToCart($product);
        foreach ($relatedProducts as $relatedProduct) {
            \PHPUnit_Framework_Assert::assertFalse(
                $checkoutCart->getCrosssellBlock()->verifyProductCrosssell($relatedProduct),
                'Product \'' . $relatedProduct->getName() . '\' is exist in cross-sell section.'
            );
        }
    }

    /**
     * Text success product is not displayed in cross-sell section
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is not displayed in cross-sell section.';
    }
}
