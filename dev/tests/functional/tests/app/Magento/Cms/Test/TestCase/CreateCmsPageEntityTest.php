<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Config\Test\Fixture\ConfigData;
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
 * @group CMS_Content
 * @ZephyrId MAGETWO-25580
 */
class CreateCmsPageEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const SEVERITY = 'S1';
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
     * Configuration data.
     *
     * @var string
     */
    private $configData;

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
     * @param string $configData
     * @return array
     */
    public function test(array $data, $fixtureType, $configData = '')
    {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        // Steps
        $cms = $this->fixtureFactory->createByCode($fixtureType, ['data' => $data]);
        $this->cmsIndex->open();
        $this->cmsIndex->getPageActionsBlock()->addNew();
        $this->cmsPageNew->getPageForm()->fill($cms);
        $this->cmsPageNew->getPageMainActions()->save();

        return ['cms' => $cms];
    }

    /**
     * Disable single store mode on config level.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->configData) {
            $this->objectManager->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => 'enable_single_store_mode', 'rollback' => true]
            )->run();
        }
    }
}
