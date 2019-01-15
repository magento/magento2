<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LayeredNavigation\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that category is present in layered navigation and product is visible in product grid.
 */
class AssertCategoryLayeredNavigation extends AbstractConstraint
{
    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Assert that category is present in layered navigation and product is visible in product grid.
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param Category $category
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        Category $category,
        BrowserInterface $browser
    ) {
        $this->browser = $browser;
        $this->openCategory($category->getDataFieldConfig('parent_id')['source']->getParentCategory());

        \PHPUnit\Framework\Assert::assertTrue(
            $catalogCategoryView->getLayeredNavigationBlock()->isCategoryVisible($category, 1),
            'Category ' . $category->getName() . ' is absent in Layered Navigation.'
        );

        $productsOnCategoryPage = $catalogCategoryView->getListProductBlock()->getProductNames();
        $productsInCategory = $category->getDataFieldConfig('category_products')['source']->getProducts();
        foreach ($productsInCategory as $product) {
            \PHPUnit\Framework\Assert::assertTrue(
                in_array($product->getName(), $productsOnCategoryPage),
                'Product ' . $product->getName() . ' is absent on category page.'
            );
        }
    }

    /**
     * Open category.
     *
     * @param Category $category
     * @return void
     */
    private function openCategory(Category $category)
    {
        $categoryUrlKey = [];

        while ($category) {
            $categoryUrlKey[] = $category->hasData('url_key')
                ? strtolower($category->getUrlKey())
                : trim(strtolower(preg_replace('#[^0-9a-z%]+#i', '-', $category->getName())), '-');

            $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
            if ($category !== null && 1 == $category->getParentId()) {
                $category = null;
            }
        }
        $categoryUrlKey = $_ENV['app_frontend_url'] . implode('/', array_reverse($categoryUrlKey)) . '.html';

        $this->browser->open($categoryUrlKey);
    }

    /**
     * Assert success message that category is present in layered navigation and product is visible in product grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category is present in layered navigation and product is visible in product grid.';
    }
}
