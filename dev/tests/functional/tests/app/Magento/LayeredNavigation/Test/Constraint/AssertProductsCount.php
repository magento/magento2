<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assertion that category name and products qty are correct in category layered navigation.
 */
class AssertProductsCount extends AbstractConstraint
{
    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Catalog category view page.
     *
     * @var CatalogCategoryView $catalogCategoryView
     */
    private $catalogCategoryView;

    /**
     * Assert that category name and products cont in layered navigation are correct.
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param Category $category
     * @param BrowserInterface $browser
     * @param string $productsCount
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        Category $category,
        BrowserInterface $browser,
        string $productsCount
    ) {
        $this->browser = $browser;
        $this->catalogCategoryView = $catalogCategoryView;
        while ($category) {
            $parentCategory = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
            if ($parentCategory && $parentCategory->getData('is_anchor') == 'No') {
                $this->openCategory($parentCategory);
                \PHPUnit\Framework\Assert::assertTrue(
                    $this->catalogCategoryView->getLayeredNavigationBlock()->isCategoryVisible(
                        $category,
                        $productsCount
                    ),
                    'Category ' . $category->getName() . ' is absent in Layered Navigation or products count is wrong'
                );
            }
            $category = $parentCategory;
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
