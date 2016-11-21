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
 * Assert that CMS pages are present on pages grid and have correct status.
 */
class AssertCmsPagesDisabledInGrid extends AbstractConstraint
{
    /**
     * Column header label.
     */
    const HEADER_LABEL = 'Status';

    /**
     * Assert that CMS pages are present on pages grid and have correct status.
     *
     * @param CmsPageIndex $cmsIndex
     * @param array $cmsPages
     * @param string $expectedStatus
     * @return void
     */
    public function processAssert(CmsPageIndex $cmsIndex, array $cmsPages, $expectedStatus)
    {
        $cmsIndex->open();
        foreach ($cmsPages as $cmsPage) {
            \PHPUnit_Framework_Assert::assertEquals(
                $expectedStatus,
                $cmsIndex->getCmsPageGridBlock()->getColumnValue($cmsPage->getPageId(), self::HEADER_LABEL),
                'Cms page \'' . $cmsPage->getTitle() . '\' status is not correct.'
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
        return 'Cms pages are present in pages grid and have correct status.';
    }
}
