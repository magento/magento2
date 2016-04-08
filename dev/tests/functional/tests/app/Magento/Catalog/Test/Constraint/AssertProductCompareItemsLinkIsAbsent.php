<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductCompareItemsLinkIsAbsent
 */
class AssertProductCompareItemsLinkIsAbsent extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert compare products link is NOT visible at the top of page.
     *
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex)
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $cmsIndex->getLinksBlock()->isLinkVisible("Compare Products"),
            'The link "Compare Products..." is visible at the top of page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'The link is NOT visible at the top of page.';
    }
}
