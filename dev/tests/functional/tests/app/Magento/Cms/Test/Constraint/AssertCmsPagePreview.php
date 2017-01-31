<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\CmsIndex as FrontCmsIndex;
use Magento\Cms\Test\Page\CmsPage as FrontCmsPage;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that content of created cms page displayed in section 'maincontent' and equals passed from fixture.
 */
class AssertCmsPagePreview extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that content of created cms page displayed in section 'maincontent' and equals passed from fixture.
     *
     * @param CmsPageIndex $cmsIndex
     * @param FrontCmsIndex $frontCmsIndex
     * @param FrontCmsPage $frontCmsPage
     * @param CmsPage $cms
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CmsPageIndex $cmsIndex,
        FrontCmsIndex $frontCmsIndex,
        FrontCmsPage $frontCmsPage,
        CmsPage $cms,
        BrowserInterface $browser
    ) {
        $cmsIndex->open();
        $filter = ['title' => $cms->getTitle()];
        $cmsIndex->getCmsPageGridBlock()->searchAndPreview($filter);
        $browser->selectWindow();

        $fixtureContent = $cms->getContent();
        \PHPUnit_Framework_Assert::assertContains(
            $fixtureContent['content'],
            $frontCmsPage->getCmsPageBlock()->getPageContent(),
            'Wrong content is displayed.'
        );
        if (isset($fixtureContent['widget'])) {
            foreach ($fixtureContent['widget']['dataset'] as $widget) {
                \PHPUnit_Framework_Assert::assertTrue(
                    $frontCmsPage->getCmsPageBlock()->isWidgetVisible($widget['widget_type'], $widget['anchor_text']),
                    'Widget \'' . $widget['widget_type'] . '\' is not displayed.'
                );
            }
        }
        if ($cms->getContentHeading()) {
            \PHPUnit_Framework_Assert::assertEquals(
                $cms->getContentHeading(),
                $frontCmsIndex->getTitleBlock()->getTitle(),
                'Wrong title is displayed.'
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
        return 'CMS Page content equals to data from fixture.';
    }
}
