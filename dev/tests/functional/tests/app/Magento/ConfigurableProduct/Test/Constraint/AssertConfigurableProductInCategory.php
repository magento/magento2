<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductInCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Checking the product in the page of its price.
 */
class AssertConfigurableProductInCategory extends AssertProductInCategory
{
    /**
     * Verify product price on category view page.
     *
     * @param FixtureInterface $product
     * @param CatalogCategoryView $catalogCategoryView
     * @return void
     */
    protected function assertPrice(FixtureInterface $product, CatalogCategoryView $catalogCategoryView)
    {
        $priceBlock = $catalogCategoryView->getListProductBlock()->getProductItem($product)->getPriceBlock();
        $priceData = $product->getDataFieldConfig('price')['source']->getPriceData();
        $price = isset($priceData['category_price']) ? $priceData['category_price'] : $product->getPrice();
        if ($priceBlock->isNormalPriceVisible()) {
            \PHPUnit\Framework\Assert::assertEquals(
                number_format($price, 2, '.', ''),
                $priceBlock->getNormalPrice(),
                'Product normal price on category page is not correct.'
            );
        } else {
            \PHPUnit\Framework\Assert::assertEquals(
                number_format($price, 2, '.', ''),
                $priceBlock->isOldPriceVisible() ? $priceBlock->getOldPrice() : $priceBlock->getPrice(),
                'Product regular price on category page is not correct.'
            );
        }
        if ($product->hasData('special_price')) {
            \PHPUnit\Framework\Assert::assertEquals(
                number_format($product->getSpecialPrice(), 2, '.', ''),
                $priceBlock->getSpecialPrice(),
                'Product special price on category page is not correct.'
            );
        }
    }
}
