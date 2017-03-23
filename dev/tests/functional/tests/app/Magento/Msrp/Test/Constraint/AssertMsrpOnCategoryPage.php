<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
class AssertMsrpOnCategoryPage extends AbstractConstraint
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
        \PHPUnit_Framework_Assert::assertTrue(
            $productBlock->isVisible(),
            'Product is invisible on Category page.'
        );

        $priceBlock = $productBlock->getPriceBlock();
        \PHPUnit_Framework_Assert::assertEquals(
            $product->getMsrp(),
            $priceBlock->getOldPrice(),
            'Displayed on Category page MAP is incorrect.'
        );
        \PHPUnit_Framework_Assert::assertFalse(
            $priceBlock->isRegularPriceVisible(),
            'Regular price on Category page is visible and not expected.'
        );

        $productBlock->openMapBlock();
        $mapBlock = $productBlock->getMapBlock();
        \PHPUnit_Framework_Assert::assertEquals(
            $product->getMsrp(),
            $mapBlock->getOldPrice(),
            'Displayed on Category page MAP is incorrect.'
        );
        $priceData = $product->getDataFieldConfig('price')['source']->getPriceData();
        $price = isset($priceData['category_price']) ? $priceData['category_price'] : $product->getPrice();
        \PHPUnit_Framework_Assert::assertEquals(
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
