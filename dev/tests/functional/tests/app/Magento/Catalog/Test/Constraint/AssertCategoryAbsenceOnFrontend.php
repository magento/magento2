<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategoryAbsenceOnFrontend
 * Assert that not displayed category in frontend main menu
 */
class AssertCategoryAbsenceOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Message on the product page 404
     */
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Assert that not displayed category in frontend main menu
     *
     * @param BrowserInterface $browser
     * @param CatalogCategoryView $categoryView
     * @param Category $category
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogCategoryView $categoryView,
        Category $category
    ) {
        $browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertEquals(
            self::NOT_FOUND_MESSAGE,
            $categoryView->getTitleBlock()->getTitle(),
            'Wrong page is displayed.'
        );
    }

    /**
     * Not found page is display
     *
     * @return string
     */
    public function toString()
    {
        return 'Not found page is display.';
    }
}
