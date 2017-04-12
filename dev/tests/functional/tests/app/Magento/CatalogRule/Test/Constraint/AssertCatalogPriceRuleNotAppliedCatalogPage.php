<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Assert that Catalog Price Rule is not applied for product(s) in Catalog.
 */
class AssertCatalogPriceRuleNotAppliedCatalogPage extends AbstractConstraint
{
    /**
     * Assert that Catalog Price Rule is not applied for product(s) in Catalog.
     *
     * @param CmsIndex $cmsIndexPage
     * @param CatalogCategoryView $catalogCategoryViewPage
     * @param array $products
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndexPage,
        CatalogCategoryView $catalogCategoryViewPage,
        array $products
    ) {
        $cmsIndexPage->open();
        foreach ($products as $product) {
            $categoryName = $product->getCategoryIds()[0];
            $cmsIndexPage->getTopmenu()->selectCategoryByName($categoryName);
            $priceBlock = $catalogCategoryViewPage->getListProductBlock()->getProductItem($product)->getPriceBlock();
            \PHPUnit_Framework_Assert::assertFalse(
                $priceBlock->isSpecialPriceVisible(),
                "Catalog price rule is applied!\n"
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Catalog price rule was not applied to products on catalog page.';
    }
}
