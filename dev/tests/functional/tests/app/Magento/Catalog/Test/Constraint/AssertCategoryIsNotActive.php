<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategoryIsNotActive
 * Assert that the category cannot be accessed from the navigation bar in the frontend
 */
class AssertCategoryIsNotActive extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Assert that the category cannot be accessed from the navigation bar in the frontend
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategory $category
     * @param Browser $browser
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, CatalogCategory $category, Browser $browser)
    {
        $cmsIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $cmsIndex->getTopmenu()->isCategoryVisible($category->getName()),
            'Category can be accessed from the navigation bar in the frontend.'
        );
        $browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertEquals(
            self::NOT_FOUND_MESSAGE,
            $cmsIndex->getTitleBlock()->getTitle(),
            'Wrong page is displayed.'
        );
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
