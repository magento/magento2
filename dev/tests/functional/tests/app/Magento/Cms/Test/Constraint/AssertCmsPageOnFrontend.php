<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\CmsPage as FrontCmsPage;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that created CMS page with expected contents displayed on Frontend.
 */
class AssertCmsPageOnFrontend extends AbstractConstraint
{
    /**
     * Assert that created CMS page with expected contents displayed on Frontend.
     *
     * @param CmsPage $cms
     * @param FrontCmsPage $frontCmsPage,
     * @param BrowserInterface $browser
     * @param string $displayContent
     * @return void
     */
    public function processAssert(
        CmsPage $cms,
        FrontCmsPage $frontCmsPage,
        BrowserInterface $browser,
        $displayContent = null
    ) {
        $browser->open($_ENV['app_frontend_url'] . $cms->getIdentifier());
        $fixtureContent = $cms->getContent();
        \PHPUnit_Framework_Assert::assertContains(
            $displayContent != null ? $displayContent : $fixtureContent['content'],
            $frontCmsPage->getCmsPageBlock()->getPageContent(),
            'Wrong content is displayed.'
        );
    }

    /**
     * CMS Page content equals to data from fixture.
     *
     * @return string
     */
    public function toString()
    {
        return 'CMS Page content equals to data from fixture.';
    }
}
