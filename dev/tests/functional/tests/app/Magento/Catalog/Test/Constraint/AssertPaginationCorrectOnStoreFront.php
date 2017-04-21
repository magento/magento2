<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\Category;

class AssertPaginationCorrectOnStoreFront extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * @param BrowserInterface $browser
     * @param Category $category
     */
    public function processAssert(BrowserInterface $browser, Category $category)
    {
        $browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is visible with HTML tags on frontend.';
    }
}
