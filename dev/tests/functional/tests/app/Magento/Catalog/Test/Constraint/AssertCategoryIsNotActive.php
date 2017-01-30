<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategoryIsNotActive
 * Assert that the category cannot be accessed from the navigation bar in the frontend
 */
class AssertCategoryIsNotActive extends AbstractConstraint
{
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Assert that the category cannot be accessed from the navigation bar in the frontend
     *
     * @param CmsIndex $cmsIndex
     * @param Category $category
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, Category $category, BrowserInterface $browser)
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
