<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductView
 */
class AssertProductView extends AbstractConstraint
{
    /**
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param CatalogProductSimple $product
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        CatalogProductSimple $product
    ) {
        //Open product view page
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        //Process assertions
        $this->assertOnProductView($product, $catalogProductView);
    }

    /**
     * Assert data on the product view page
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    protected function assertOnProductView(CatalogProductSimple $product, CatalogProductView $catalogProductView)
    {
        $viewBlock = $catalogProductView->getViewBlock();
        $price = $viewBlock->getPriceBlock()->getPrice();
        $name = $viewBlock->getProductName();
        $sku = $viewBlock->getProductSku();

        \PHPUnit\Framework\Assert::assertEquals(
            $product->getName(),
            $name,
            'Product name on product view page is not correct.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $product->getSku(),
            $sku,
            'Product sku on product view page is not correct.'
        );

        if (isset($price['price_regular_price'])) {
            \PHPUnit\Framework\Assert::assertEquals(
                number_format($product->getPrice(), 2),
                $price['price_regular_price'],
                'Product regular price on product view page is not correct.'
            );
        }

        $priceComparing = false;
        if ($specialPrice = $product->getSpecialPrice()) {
            $priceComparing = $specialPrice;
        }

        if ($priceComparing && isset($price['price_special_price'])) {
            \PHPUnit\Framework\Assert::assertEquals(
                number_format($priceComparing, 2),
                $price['price_special_price'],
                'Product special price on product view page is not correct.'
            );
        }
    }

    /**
     * Text of Visible in category assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Product data on product view page is not correct.';
    }
}
