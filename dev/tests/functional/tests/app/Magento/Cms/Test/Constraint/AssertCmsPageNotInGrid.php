<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert Cms page is absent in grid.
 */
class AssertCmsPageNotInGrid extends AbstractConstraint
{
    /**
     * Assert that Cms page is not present in pages grid.
     *
     * @param CmsPageIndex $cmsIndex
     * @param CmsPage $cmsPage
     * @return void
     */
    public function processAssert(CmsPageIndex $cmsIndex, CmsPage $cmsPage)
    {
        $filter = [
            'title' => $cmsPage->getTitle(),
        ];
        \PHPUnit\Framework\Assert::assertFalse(
            $cmsIndex->getCmsPageGridBlock()->isRowVisible($filter),
            'Cms page \'' . $cmsPage->getTitle() . '\' is present in pages grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cms page is not present in pages grid.';
    }
}
