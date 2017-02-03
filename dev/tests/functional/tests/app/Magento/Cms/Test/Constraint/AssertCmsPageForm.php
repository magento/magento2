<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert that displayed CMS page data on edit page equals passed from fixture.
 */
class AssertCmsPageForm extends AbstractAssertForm
{
    /**
     * Skipped fields for verify data.
     *
     * @var array
     */
    protected $skippedFields = [
        'page_id',
        'content',
        'content_heading',
        'custom_theme_from',
        'custom_theme_to',
    ];

    /**
     * Assert that displayed CMS page data on edit page equals passed from fixture.
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
        $cmsFormData['store_id'] = implode('/', $cmsFormData['store_id']);
        $errors = $this->verifyData($cms->getData(), $cmsFormData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * CMS page data on edit page equals data from fixture.
     *
     * @return string
     */
    public function toString()
    {
        return 'CMS page data on edit page equals data from fixture.';
    }
}
