<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;

/**
 * Assert that displayed CMS page data on edit page equals passed from fixture.
 */
class AssertCmsPageFormSingleStoreMode extends AssertCmsPageForm
{
    /**
     * Assert that displayed CMS page data on edit page equals passed from fixture with enabled single store mode.
     *
     * @param CmsPage $cms
     * @param CmsPageIndex $cmsIndex
     * @param CmsPageNew $cmsPageNew
     * @return void
     */
    public function processAssert(
        CmsPage $cms,
        CmsPageIndex $cmsIndex,
        CmsPageNew $cmsPageNew
    ) {
        $cmsIndex->open();
        $filter = ['title' => $cms->getTitle()];
        $cmsIndex->getCmsPageGridBlock()->searchAndOpen($filter);

        $cmsFormData = $cmsPageNew->getPageForm()->getData($cms);
        $cmsFixtureData = $cms->getData();
        $errors = $this->verifyData($cmsFixtureData, $cmsFormData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
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
