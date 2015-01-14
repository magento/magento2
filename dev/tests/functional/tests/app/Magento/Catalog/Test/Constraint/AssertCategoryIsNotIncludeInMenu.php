<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategoryIsNotIncludeInMenu
 * Assert that the category is no longer available on the top menu bar
 */
class AssertCategoryIsNotIncludeInMenu extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that the category is no longer available on the top menu bar
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategory $category
     * @param Browser $browser
     * @param CatalogCategoryView $categoryView
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategory $category,
        Browser $browser,
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
                    $categoryView->getListProductBlock()->isProductVisible($productFixture->getName()),
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
