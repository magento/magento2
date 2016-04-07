<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that CMS page present in grid and can be found by title.
 */
class AssertCmsPageInGrid extends AbstractConstraint
{
    /**
     * Assert that cms page is present in pages grid.
     *
     * @param CmsPageIndex $cmsIndex
     * @param CmsPage $cms
     * @return void
     */
    public function processAssert(CmsPageIndex $cmsIndex, CmsPage $cms)
    {
        $filter = [
            'title' => $cms->getTitle(),
        ];
        $cmsIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $cmsIndex->getCmsPageGridBlock()->isRowVisible($filter, true, false),
            'Cms page \'' . $cms->getTitle() . '\' is not present in pages grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cms page is present in pages grid.';
    }
}
