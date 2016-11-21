<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\TestCase;

use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create two CMS pages.
 *
 * Steps:
 * 1. Log in to Backend.
 * 2. Navigate to Content > Elements > Pages.
 * 3. Perform mass action on the newly created pages.
 * 4. Perform assertions.
 *
 * @group CMS_Content
 * @ZephyrId MAGETWO-35581
 */
class CmsPageMassActionTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * CmsIndex page.
     *
     * @var CmsPageIndex
     */
    protected $cmsIndex;

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
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(CmsPageIndex $cmsIndex, FixtureFactory $fixtureFactory)
    {
        $this->cmsIndex = $cmsIndex;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Creating Cms page.
     *
     * @param array $cmsPages
     * @param string $action
     * @return array
     */
    public function test(array $cmsPages, $action)
    {
        // Preconditions
        $pages = [];
        $pagesForMassAction = [];
        foreach ($cmsPages as $key => $dataset) {
            $pages[$key] = $this->fixtureFactory->createByCode('cmsPage', ['dataset' => $dataset]);
            $pages[$key]->persist();
            $pagesForMassAction[$key] = ['id' => $pages[$key]->getPageId()];
        }

        // Test steps
        $this->cmsIndex->open();
        $this->cmsIndex->getCmsPageGridBlock()->massaction($pagesForMassAction, $action);

        return [
            'cmsPages' => $pages
        ];
    }
}
