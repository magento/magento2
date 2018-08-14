<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;

/**
 * Assert that url rewrite category in grid.
 */
class AssertUrlRewritesRedirectInGrid extends AbstractConstraint
{
    /**
     * Assert that url rewrite category in grid.
     *
     * @param AssertUrlRewriteRedirectInGrid $assertUrlRewriteRedirectInGrid
     * @param array $categories
     * @param array $categoriesBeforeSave
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param string $redirectType [optional]
     * @return void
     */
    public function processAssert(
        AssertUrlRewriteRedirectInGrid $assertUrlRewriteRedirectInGrid,
        array $categories,
        array $categoriesBeforeSave,
        UrlRewriteIndex $urlRewriteIndex,
        $redirectType = 'Permanent (301)'
    ) {
        foreach ($categories as $key => $category) {
            $assertUrlRewriteRedirectInGrid->processAssert(
                $category,
                $categoriesBeforeSave[$key],
                $urlRewriteIndex,
                $key,
                $redirectType
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category redirects a present in grid.';
    }
}
