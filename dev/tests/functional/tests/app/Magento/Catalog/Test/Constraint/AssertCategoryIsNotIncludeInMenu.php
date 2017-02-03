<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategoryIsNotIncludeInMenu
 * Assert that the category is no longer available on the top menu bar
 */
class AssertCategoryIsNotIncludeInMenu extends AbstractConstraint
{
    /**
     * Assert that the category is no longer available on the top menu bar
     *
     * @param CmsIndex $cmsIndex
     * @param Category $category
     * @param BrowserInterface $browser
     * @param CatalogCategoryView $categoryView
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        Category $category,
        BrowserInterface $browser,
        CatalogCategoryView $categoryView
    ) {
        $cmsIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $cmsIndex->getTopmenu()->isCategoryVisible($category->getName()),
            'Category can be accessed from the navigation bar in the frontend.'
        );

        $browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertEquals(
            $category->getName(),
            $categoryView->getTitleBlock()->getTitle(),
            'Wrong page is displayed.'
        );
        if (isset($category->getDataFieldConfig('category_products')['source'])) {
            $products = $category->getDataFieldConfig('category_products')['source']->getProducts();
            foreach ($products as $productFixture) {
                \PHPUnit_Framework_Assert::assertTrue(
                    $categoryView->getListProductBlock()->getProductItem($productFixture)->isVisible(),
                    "Products '{$productFixture->getName()}' not find."
                );
            }
        }
    }

    /**
     * Category is no longer available on the top menu bar, but can be viewed by URL with all assigned products
     *
     * @return string
     */
    public function toString()
    {
        return 'Category is not on the top menu bar, but can be viewed by URL.';
    }
}
