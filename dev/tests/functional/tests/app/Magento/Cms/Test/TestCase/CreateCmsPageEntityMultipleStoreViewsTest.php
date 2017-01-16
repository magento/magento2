<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Log in to Backend.
 * 2. Navigate to Content > Elements > Pages.
 * 3. Click "Add New Page", add page contents according to Test Data(Default Store View) and save.
 * 4. Click "Add New Page", add page contents according to Test Data(Custom Store View 1) and save.
 * 5. Click "Add New Page", add page contents according to Test Data(Custom Store View 2) and save.
 * 6. Save CMS Page.
 * 7. Verify created CMS Page.
 *
 * @group CMS_Content
 * @ZephyrId MAGETWO-52467
 */
class CreateCmsPageEntityMultipleStoreViewsTest extends Injectable
{
    /**
     * CmsIndex page.
     *
     * @var CmsPageIndex
     */
    protected $cmsIndex;

    /**
     * CmsPageNew page.
     *
     * @var CmsPageNew
     */
    protected $cmsPageNew;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject pages.
     *
     * @param CmsPageIndex $cmsIndex
     * @param CmsPageNew $cmsPageNew
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(CmsPageIndex $cmsIndex, CmsPageNew $cmsPageNew, FixtureFactory $fixtureFactory)
    {
        $this->cmsIndex = $cmsIndex;
        $this->cmsPageNew = $cmsPageNew;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Page cache for different CMS pages on multiple store views.
     *
     * @param array $cmsPages
     * @return array
     */
    public function test(array $cmsPages)
    {
        // Steps
        $cmsPageFixtures = [];
        foreach ($cmsPages as $cmsPage) {
            $cmsPageFixture = $this->fixtureFactory->createByCode('cmsPage', ['data' => $cmsPage]);
            $cmsPageFixtures[] = $cmsPageFixture;

            $this->cmsIndex->open();
            $this->cmsIndex->getPageActionsBlock()->addNew();
            $this->cmsPageNew->getPageForm()->fill($cmsPageFixture);
            $this->cmsPageNew->getPageMainActions()->save();
        }

        return ['cmsPages' => $cmsPageFixtures];
    }
}
