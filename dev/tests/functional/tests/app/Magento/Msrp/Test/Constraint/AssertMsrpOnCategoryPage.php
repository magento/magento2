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
 * Assert product MSRP related data on category page.
 */
class AssertMsrpOnCategoryPage extends AbstractConstraint
{
    /**
     * Assert product MSRP related data on category page.
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
        \PHPUnit\Framework\Assert::assertTrue(
            $productBlock->isVisible(),
            'Product is invisible on Category page.'
        );

        $priceBlock = $productBlock->getPriceBlock();
        \PHPUnit\Framework\Assert::assertEquals(
            $product->getMsrp(),
            $priceBlock->getOldPrice(),
            'Displayed on Category page MSRP is incorrect.'
        );
        \PHPUnit\Framework\Assert::assertFalse(
            $priceBlock->isRegularPriceVisible(),
            'Regular price on Category page is visible and not expected.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return "Displayed Product MSRP data on category page is correct.";
    }
}
