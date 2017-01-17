<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert that category is present on frontend.
 */
class AssertCategoryOnFrontend extends AbstractConstraint
{
    /**
     * Assert that category is present on frontend.
     *
     * @param BrowserInterface $browser
     * @param CatalogCategoryView $categoryView
     * @param Category $category
     * @param Category $initialCategory
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogCategoryView $categoryView,
        Category $category,
        Category $initialCategory,
        CmsIndex $cmsIndex
    ) {
        $cmsIndex->open();
        $cmsIndex->getLinksBlock()->waitWelcomeMessage();

        $name = $category && $category->getName() ?: $initialCategory->getName();
        $isMenuCategory = $category && $category->getIncludeInMenu() ?: $initialCategory->getIncludeInMenu();
        $categoryUrl = $_ENV['app_frontend_url'] . $initialCategory->getUrlKey() . '.html';

        if ($isMenuCategory) {
            \PHPUnit_Framework_Assert::assertTrue(
                $cmsIndex->getTopmenu()->isCategoryVisible($name),
                'Category is not displayed in top menu.'
            );
            $cmsIndex->getTopmenu()->selectCategoryByName($name);
        } else {
            $browser->open($categoryUrl);
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $categoryUrl,
            $browser->getUrl(),
            'Wrong category is displayed.'
        );

        \PHPUnit_Framework_Assert::assertEquals(
            $name,
            $categoryView->getTitleBlock()->getTitle(),
            'Wrong category name is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category is present on frontend.';
    }
}
