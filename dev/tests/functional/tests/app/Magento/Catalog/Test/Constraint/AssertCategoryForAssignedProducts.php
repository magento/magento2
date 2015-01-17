<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategoryForAssignedProducts
 * Assert that displayed assigned products on category page equals passed from fixture
 */
class AssertCategoryForAssignedProducts extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that displayed assigned products on category page equals passed from fixture
     *
     * @param CatalogCategory $category
     * @param CatalogCategoryView $categoryView
     * @param Browser $browser
     * @return void
     */
    public function processAssert(CatalogCategory $category, CatalogCategoryView $categoryView, Browser $browser)
    {
        $browser->open($_ENV['app_frontend_url'] . strtolower($category->getUrlKey()) . '.html');
        $products = $category->getDataFieldConfig('category_products')['source']->getProducts();
        foreach ($products as $productFixture) {
            \PHPUnit_Framework_Assert::assertTrue(
                $categoryView->getListProductBlock()->isProductVisible($productFixture->getName()),
                "Products '{$productFixture->getName()}' not find."
            );
        }
    }

    /**
     * Displayed assigned products on category page equals passed from fixture
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed assigned products on category page equal to passed from fixture.';
    }
}
