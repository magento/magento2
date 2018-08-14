<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\EditWebsite;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Update Website (Store Management)
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create website
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Stores-> All Stores
 * 3. Open created website
 * 4. Fill data according to dataset
 * 5. Click "Save Web Site" button
 * 6. Perform all assertions
 *
 * @group Store_Management
 * @ZephyrId MAGETWO-27690
 */
class UpdateWebsiteEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * Page StoreIndex
     *
     * @var StoreIndex
     */
    protected $storeIndex;

    /**
     * Page EditWebsite
     *
     * @var EditWebsite
     */
    protected $editWebsite;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Injection data
     *
     * @param StoreIndex $storeIndex
     * @param EditWebsite $editWebsite
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        StoreIndex $storeIndex,
        EditWebsite $editWebsite,
        FixtureFactory $fixtureFactory
    ) {
        $this->storeIndex = $storeIndex;
        $this->editWebsite = $editWebsite;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Update Website
     *
     * @param Website $websiteOrigin
     * @param Website $website
     * @return array
     */
    public function test(Website $websiteOrigin, Website $website)
    {
        //Preconditions
        $websiteOrigin->persist();

        //Steps
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpenWebsite($websiteOrigin);
        $this->editWebsite->getEditFormWebsite()->fill($website);
        $this->editWebsite->getFormPageActions()->save();

        return ['website' => $this->mergeFixture($website, $websiteOrigin)];
    }

    /**
     * Merge Website fixtures
     *
     * @param Website $website
     * @param Website $websiteOrigin
     * @return Website
     */
    protected function mergeFixture(Website $website, Website $websiteOrigin)
    {
        $data = array_merge($websiteOrigin->getData(), $website->getData());
        return $this->fixtureFactory->createByCode('website', ['data' => $data]);
    }
}
