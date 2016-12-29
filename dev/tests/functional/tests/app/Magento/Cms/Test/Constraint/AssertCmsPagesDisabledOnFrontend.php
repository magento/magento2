<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\CmsIndex as FrontCmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that created CMS pages with 'Status' - Disabled display with '404 Not Found' message on Frontend.
 */
class AssertCmsPagesDisabledOnFrontend extends AbstractConstraint
{
    /**
     * Assert that created CMS pages with 'Status' - Disabled display with '404 Not Found' message on Frontend.
     *
     * @param FrontCmsIndex $frontCmsIndex
     * @param CmsPageIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param AssertCmsPageDisabledOnFrontend $assertCmsPageDisabledOnFrontend
     * @param array $cmsPages
     * @return void
     */
    public function processAssert(
        FrontCmsIndex $frontCmsIndex,
        CmsPageIndex $cmsIndex,
        BrowserInterface $browser,
        AssertCmsPageDisabledOnFrontend $assertCmsPageDisabledOnFrontend,
        array $cmsPages
    ) {
        foreach ($cmsPages as $cmsPage) {
            $assertCmsPageDisabledOnFrontend->processAssert($cmsPage, $frontCmsIndex, $cmsIndex, $browser);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Pages with message "404 Not Found" are displayed.';
    }
}
