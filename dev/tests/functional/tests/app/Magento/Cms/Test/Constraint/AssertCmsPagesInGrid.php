<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that CMS pages are present in grid and can be found by title and status.
 */
class AssertCmsPagesInGrid extends AbstractConstraint
{
    /**
     * Assert that cms pages are present in pages grid.
     *
     * @param CmsPageIndex $cmsIndex
     * @param AssertCmsPageInGrid $assertCmsPageInGrid
     * @param array $cmsPages
     * @param string $expectedStatus
     * @return void
     */
    public function processAssert(
        CmsPageIndex $cmsIndex,
        AssertCmsPageInGrid $assertCmsPageInGrid,
        $cmsPages,
        $expectedStatus
    ) {
        foreach ($cmsPages as $cmsPage) {
            $assertCmsPageInGrid->processAssert($cmsIndex, $cmsPage, $expectedStatus);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cms pages are present in pages grid.';
    }
}
