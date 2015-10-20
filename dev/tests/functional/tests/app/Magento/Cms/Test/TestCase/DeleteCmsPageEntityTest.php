<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. CMS Page is created.
 *
 * Steps:
 * 1. Log in to Backend.
 * 2. Navigate to CONTENT > Pages.
 * 3. Click on CMS Page from grid.
 * 4. Click "Delete Page" button.
 * 5. Perform all assertions.
 *
 * @group CMS_Content_(PS)
 * @ZephyrId MAGETWO-23291
 */
class DeleteCmsPageEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * CMS Index page.
     *
     * @var CmsPageIndex
     */
    protected $cmsPageIndex;

    /**
     * Edit CMS page.
     *
     * @var CmsPageNew
     */
    protected $cmsPageNew;

    /**
     * Inject pages.
     *
     * @param CmsPageIndex $cmsPageIndex
     * @param CmsPageNew $cmsPageNew
     * @return void
     */
    public function __inject(CmsPageIndex $cmsPageIndex, CmsPageNew $cmsPageNew)
    {
        $this->cmsPageIndex = $cmsPageIndex;
        $this->cmsPageNew = $cmsPageNew;
    }

    /**
     * Delete CMS Page.
     *
     * @param CmsPage $cmsPage
     * @return void
     */
    public function test(CmsPage $cmsPage)
    {
        // Preconditions
        $cmsPage->persist();

        // Steps
        $this->cmsPageIndex->open();
        $this->cmsPageIndex->getCmsPageGridBlock()->searchAndOpen(['title' => $cmsPage->getTitle()]);
        $this->cmsPageNew->getPageMainActions()->delete();
        $this->cmsPageNew->getModalBlock()->acceptAlert();
    }
}
