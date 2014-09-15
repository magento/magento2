<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Store\Test\TestCase;

use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Store\Test\Fixture\Website;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\EditWebsite;

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
 * @group Store_Management_(PS)
 * @ZephyrId MAGETWO-27690
 */
class UpdateWebsiteEntityTest extends Injectable
{
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
