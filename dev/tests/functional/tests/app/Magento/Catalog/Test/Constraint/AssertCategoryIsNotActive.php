<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Class AssertCategoryIsNotActive
 * Assert that the category cannot be accessed using the direct URL and from the navigation bar in the frontend
 */
class AssertCategoryIsNotActive extends AbstractConstraint
{
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Assert that the category cannot be accessed using the direct URL and from the navigation bar in the frontend
     *
     * @param Category $category
     * @param CatalogCategoryView $categoryView
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        Category $category,
        CatalogCategoryView $categoryView,
        BrowserInterface $browser
    ) {
        $browser->open($this->getCategoryUrl($category));
        \PHPUnit_Framework_Assert::assertFalse(
            $categoryView->getTopmenu()->isCategoryVisible($category->getName()),
            'Category can be accessed from the navigation bar in the frontend.'
        );
        \PHPUnit_Framework_Assert::assertEquals(
            self::NOT_FOUND_MESSAGE,
            $categoryView->getTitleBlock()->getTitle(),
            'Wrong page is displayed.'
        );
    }

    /**
     * Get category url to open.
     *
     * @param Category $category
     * @return string
     */
    protected function getCategoryUrl(Category $category)
    {
        $categoryUrlKey = [];
        while ($category) {
            $categoryUrlKey[] = $category->hasData('url_key')
                ? strtolower($category->getUrlKey())
                : trim(strtolower(preg_replace('#[^0-9a-z%]+#i', '-', $category->getName())), '-');

            $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
            if (1 == $category->getParentId()) {
                $category = null;
            }
        }

        return $_ENV['app_frontend_url'] . implode('/', array_reverse($categoryUrlKey)) . '.html';
    }


    /**
     * Category not find in top menu
     *
     * @return string
     */
    public function toString()
    {
        return 'Category cannot be accessed from the navigation bar.';
    }
}
