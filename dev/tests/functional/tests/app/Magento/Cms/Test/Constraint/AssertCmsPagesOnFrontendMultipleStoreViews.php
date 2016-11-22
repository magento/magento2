<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Page\CmsPage as FrontCmsPage;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that created CMS page with expected contents displayed on store view.
 */
class AssertCmsPagesOnFrontendMultipleStoreViews extends AbstractConstraint
{
    /**
     * Assert that created CMS page with expected contents displayed on store view.
     *
     * @param array $cmsPages
     * @param FrontCmsPage $frontCmsPage,
     * @param CmsIndex $cmsIndex,
     * @param BrowserInterface $browser
     * @param string $displayContent
     * @return void
     */
    public function processAssert(
        array $cmsPages,
        FrontCmsPage $frontCmsPage,
        CmsIndex $cmsIndex,
        BrowserInterface $browser,
        $displayContent = null
    ) {
        foreach ($cmsPages as $cmsPage) {
            $browser->open($_ENV['app_frontend_url'] . $cmsPage->getIdentifier());
            $storeName = $cmsPage->getDataFieldConfig('store_id')['source']->getStore()->getData()['name'];
            $cmsIndex->getStoreSwitcherBlock()->selectStoreView($storeName);
            \PHPUnit_Framework_Assert::assertContains(
                $displayContent != null ? $displayContent : $cmsPage->getContent()['content'],
                $frontCmsPage->getCmsPageBlock()->getPageContent(),
                'Wrong content page ' . $cmsPage->getTitle() . ' is displayed on store ' . $storeName . '.'
            );
        }
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
