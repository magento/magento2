<?php
/**
 * Copyright © Magento, Inc. All rights reserved
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\CmsPage as FrontCmsPage;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that widget title equals passed from fixture.
 */
class AssertCmsWidgetTitle extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that widget title equals passed from fixture.
     *
     * @param CmsPageIndex $cmsIndex
     * @param FrontCmsPage $frontCmsPage
     * @param CmsPage $cms
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CmsPageIndex $cmsIndex,
        FrontCmsPage $frontCmsPage,
        CmsPage $cms,
        BrowserInterface $browser
    ) {
        $cmsIndex->open();
        $filter = ['title' => $cms->getTitle()];
        $cmsIndex->getCmsPageGridBlock()->searchAndPreview($filter);
        $browser->selectWindow();

        $fixtureContent = $cms->getContent();
        
        if (isset($fixtureContent['widget'])) {
            foreach ($fixtureContent['widget']['dataset'] as $widget) {
                \PHPUnit_Framework_Assert::assertEquals(
                    $widget['title'],
                    $frontCmsPage->getCmsPageBlock()->getWidgetTitle($widget['widget_type'], $widget['anchor_text']),
                    "Widget title wasn't properly saved."
                );
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Widget title equals to data from fixture.';
    }
}
