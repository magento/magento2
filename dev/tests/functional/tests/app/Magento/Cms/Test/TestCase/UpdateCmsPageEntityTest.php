<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. CMS Page is created.
 *
 * Steps:
 * 1. Log in to Backend.
 * 2. Navigate to Content > Elements > Pages.
 * 3. Click on CMS Page from grid.
 * 4. Edit test value(s) according to data set.
 * 5. Click 'Save' CMS Page.
 * 6. Perform asserts.
 *
 * @group CMS_Content
 * @ZephyrId MAGETWO-25186
 */
class UpdateCmsPageEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S1';
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
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $factory;

    /**
     * Inject page.
     *
     * @param CmsPageIndex $cmsPageIndex
     * @param CmsPageNew $cmsPageNew
     * @param CmsPage $cmsOriginal
     * @param FixtureFactory $factory
     * @return array
     */
    public function __inject(
        CmsPageIndex $cmsPageIndex,
        CmsPageNew $cmsPageNew,
        CmsPage $cmsOriginal,
        FixtureFactory $factory
    ) {
        $cmsOriginal->persist();
        $this->cmsPageIndex = $cmsPageIndex;
        $this->cmsPageNew = $cmsPageNew;
        $this->factory = $factory;
        return ['cmsOriginal' => $cmsOriginal];
    }

    /**
     * Update CMS Page.
     *
     * @param CmsPage $cms
     * @param CmsPage $cmsOriginal
     * @return array
     */
    public function test(CmsPage $cms, CmsPage $cmsOriginal)
    {
        // Steps
        $this->cmsPageIndex->open();
        $this->cmsPageIndex->getCmsPageGridBlock()->searchAndOpen(['title' => $cmsOriginal->getTitle()]);
        $this->cmsPageNew->getPageForm()->fill($cms);
        $this->cmsPageNew->getPageMainActions()->save();

        return [
            'cms' => $this->factory->createByCode(
                'cmsPage',
                ['data' => array_merge($cmsOriginal->getData(), $cms->getData())]
            )
        ];
    }
}
