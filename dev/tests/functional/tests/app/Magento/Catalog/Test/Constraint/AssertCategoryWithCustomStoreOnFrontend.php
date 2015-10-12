<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert that category with custom store visible on frontend.
 */
class AssertCategoryWithCustomStoreOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that category with custom store visible on frontend.
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
        $browser->open($_ENV['app_frontend_url'] . $initialCategory->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertEquals(
            $initialCategory->getName(),
            $categoryView->getTitleBlock()->getTitle(),
            'Wrong page is displayed on default store.'
        );

        $store = $category->getDataFieldConfig('store_id')['source']->store->getName();
        $cmsIndex->getStoreSwitcherBlock()->selectStoreView($store);
        $cmsIndex->getLinksBlock()->waitWelcomeMessage();
        $browser->open($_ENV['app_frontend_url'] . $initialCategory->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertEquals(
            $category->getName(),
            $categoryView->getTitleBlock()->getTitle(),
            'Wrong page is displayed on ' . $store
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category with custom store visible on frontend.';
    }
}
