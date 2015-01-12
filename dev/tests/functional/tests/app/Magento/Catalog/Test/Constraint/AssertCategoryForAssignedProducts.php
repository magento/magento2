<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
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
     * @param Category $category
     * @param CatalogCategoryView $categoryView
     * @param Browser $browser
     * @return void
     */
    public function processAssert(Category $category, CatalogCategoryView $categoryView, Browser $browser)
    {
        $categoryUrlKey = $category->hasData('url_key')
            ? strtolower($category->getUrlKey())
            : trim(strtolower(preg_replace('#[^0-9a-z%]+#i', '-', $category->getName())), '-');
        $products = $category->getDataFieldConfig('category_products')['source']->getProducts();

        $browser->open($_ENV['app_frontend_url'] . $categoryUrlKey . '.html');
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
