<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\CmsIndex as FrontCmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that created CMS page with 'Status' - Disabled displays with '404 Not Found' message on Frontend.
 */
class AssertCmsPageDisabledOnFrontend extends AbstractConstraint
{
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Assert that created CMS page with 'Status' - Disabled displays with '404 Not Found' message on Frontend.
     *
     * @param CmsPage $cms
     * @param FrontCmsIndex $frontCmsIndex
     * @param CmsPageIndex $cmsIndex
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CmsPage $cms,
        FrontCmsIndex $frontCmsIndex,
        CmsPageIndex $cmsIndex,
        BrowserInterface $browser
    ) {
        $cmsIndex->open();
        $filter = ['title' => $cms->getTitle()];
        $cmsIndex->getCmsPageGridBlock()->searchAndPreview($filter);
        $browser->selectWindow();
        \PHPUnit_Framework_Assert::assertEquals(
            self::NOT_FOUND_MESSAGE,
            $frontCmsIndex->getTitleBlock()->getTitle(),
            'Wrong page is displayed.'
        );
    }

    /**
     * Not found page is display.
     *
     * @return string
     */
    public function toString()
    {
        return 'Not found page is display.';
    }
}
