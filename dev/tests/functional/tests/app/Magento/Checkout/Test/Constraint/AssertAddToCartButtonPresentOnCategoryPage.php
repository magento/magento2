<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Checks that "Add to Cart" button is visible on category page.
 */
class AssertAddToCartButtonPresentOnCategoryPage extends AbstractConstraint
{
    /**
     * Assert that "Add to Cart" button is present on category page.
     *
     * @param InjectableFixture $product
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param Category|null $category [optional]
     * @return void
     */
    public function processAssert(
        InjectableFixture $product,
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        Category $category = null
    ) {
        $cmsIndex->open();
        $categoryName = $category === null ? $product->getCategoryIds()[0] : $category->getName();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);

        $isProductVisible = $catalogCategoryView->getListProductBlock()->getProductItem($product)->isVisible();
        while (!$isProductVisible && $catalogCategoryView->getBottomToolbar()->nextPage()) {
            $isProductVisible = $catalogCategoryView->getListProductBlock()->getProductItem($product)->isVisible();
        }
        \PHPUnit\Framework\Assert::assertTrue($isProductVisible, 'Product is absent on category page.');

        \PHPUnit\Framework\Assert::assertTrue(
            $catalogCategoryView->getListProductBlock()->getProductItem($product)->isVisibleAddToCardButton(),
            'Button "Add to Cart" is absent on category page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Button "Add to Cart" is present on category page.';
    }
}
