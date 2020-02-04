<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategoryRedirect
 * Assert that old Category URL lead to appropriate Category in frontend
 */
class AssertCategoryRedirect extends AbstractConstraint
{
    /**
     * Assert that old Category URL lead to appropriate Category in frontend
     *
     * @param Category $category
     * @param BrowserInterface $browser
     * @param Category $initialCategory
     * @return void
     */
    public function processAssert(
        Category $category,
        BrowserInterface $browser,
        Category $initialCategory
    ) {
        $browser->open($_ENV['app_frontend_url'] . $initialCategory->getUrlKey() . '.html');

        \PHPUnit\Framework\Assert::assertEquals(
            $browser->getUrl(),
            $_ENV['app_frontend_url'] . strtolower($category->getUrlKey()) . '.html',
            'URL rewrite category redirect false.'
        );
    }

    /**
     * URL rewrite category redirect success
     *
     * @return string
     */
    public function toString()
    {
        return 'URL rewrite category redirect success.';
    }
}
