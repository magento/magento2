<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Assert product MAP related data on category page.
 */
class AssertMapOnCategoryPage extends AbstractConstraint
{
    /**
     * Assert product MAP related data on category page.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        InjectableFixture $product
    ) {
        /** @var CatalogProductSimple $product */
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);

        $productBlock = $catalogCategoryView->getMsrpListProductBlock()->getProductItem($product);
        $productBlock->openMapBlock();
        $mapBlock = $productBlock->getMapBlock();
        \PHPUnit\Framework\Assert::assertEquals(
            $product->getMsrp(),
            $mapBlock->getOldPrice(),
            'Displayed on Category page MAP is incorrect.'
        );
        $priceData = $product->getDataFieldConfig('price')['source']->getPriceData();
        $price = isset($priceData['category_price']) ? $priceData['category_price'] : $product->getPrice();
        \PHPUnit\Framework\Assert::assertEquals(
            $price,
            $mapBlock->getActualPrice(),
            'Displayed on Category page price is incorrect.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return "Displayed Product MAP data on category page is correct.";
    }
}
