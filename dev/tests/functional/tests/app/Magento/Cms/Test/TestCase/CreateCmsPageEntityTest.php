<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Cms\Test\Fixture\CmsPage as CmsPageFixture;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Log in to Backend.
 * 2. Navigate to Content > Elements > Pages.
 * 3. Start to create new CMS Page.
 * 4. Fill out fields data according to data set.
 * 5. Save CMS Page.
 * 6. Verify created CMS Page.
 *
 * @group CMS_Content_(PS)
 * @ZephyrId MAGETWO-25580
 */
class CreateCmsPageEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

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
     * Creating Cms page.
     *
     * @param array $data
     * @param string $fixtureType
     * @return array
     */
    public function test(array $data, $fixtureType)
    {
        // Steps
        $cms = $this->fixtureFactory->createByCode($fixtureType, ['data' => $data]);
        $this->cmsIndex->open();
        $this->cmsIndex->getPageActionsBlock()->addNew();
        //TODO: remove cms page new refresh after resolve issue with static js files publication (MAGETWO-37898)
        $this->cmsPageNew->open();
        $this->cmsPageNew->getPageForm()->fill($cms);
        $this->cmsPageNew->getPageMainActions()->save();

        return ['cms' => $cms];
    }
}
