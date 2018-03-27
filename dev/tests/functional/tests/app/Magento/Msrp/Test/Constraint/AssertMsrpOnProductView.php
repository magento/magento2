<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Assert product MSRP related data on product view page.
 */
class AssertMsrpOnProductView extends AbstractConstraint
{
    /**
     * Assert product MSRP related data on product view page.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductView $catalogProductView
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductView $catalogProductView,
        InjectableFixture $product
    ) {
        /** @var CatalogProductSimple $product */
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);
        $catalogCategoryView->getListProductBlock()->getProductItem($product)->open();

        $viewBlock = $catalogProductView->getMsrpViewBlock();
        $priceBlock = $viewBlock->getPriceBlock();
        \PHPUnit\Framework\Assert::assertEquals(
            $product->getMsrp(),
            $priceBlock->getOldPrice(),
            'Displayed on Product view page MSRP is incorrect'
        );
        \PHPUnit\Framework\Assert::assertFalse(
            $priceBlock->isRegularPriceVisible(),
            'Regular price on Product view page is visible and not expected.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return "Displayed Product MSRP data on product view page is correct.";
    }
}
