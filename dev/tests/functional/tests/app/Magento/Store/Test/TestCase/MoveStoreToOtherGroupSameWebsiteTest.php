<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\EditStore;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Test Move Store view to another store within same website (Store Management)
 *
 * Test Flow:
 *
 * Preconditions:
 * 1.Create store STA with store view SVA
 * 2.Create store STB with store view SVB
 * 3.STA and STB belong to the same website
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Stores -> All Stores
 * 3. Open store view SVB from grid
 * 4. Change store group setting from STB to STA
 * 5. Save store entity
 * 6. Perform all assertions
 *
 * @group Store_Management
 * @ZephyrId MAGETWO-54026
 */
class MoveStoreToOtherGroupSameWebsiteTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Page StoreIndex
     *
     * @var StoreIndex
     */
    protected $storeIndex;

    /**
     * Page EditStore
     *
     * @var EditStore
     */
    protected $editStore;

    /**
     * Preparing pages for test
     *
     * @param StoreIndex $storeIndex
     * @param EditStore $editStore
     * @return void
     */
    public function __inject(StoreIndex $storeIndex, EditStore $editStore)
    {
        $this->storeIndex = $storeIndex;
        $this->editStore = $editStore;
    }

    /**
     * Move store view to another store group within a website
     *
     * @param FixtureFactory $fixtureFactory
     * @param Store $storeInitialA
     * @param Store $storeInitialB
     * @return array
     */
    public function test(FixtureFactory $fixtureFactory, Store $storeInitialA, Store $storeInitialB)
    {
        // Prepare data for constraints
        $store = $fixtureFactory->createByCode(
            'store',
            [
                'data' => [
                    'name' => $storeInitialB->getName(),
                    'code' => $storeInitialB->getCode(),
                    'is_active' => $storeInitialB->getIsActive(),
                    'group_id' => [
                        'storeGroup' => $storeInitialA->getDataFieldConfig('group_id')['source']->getStoreGroup()
                    ],
                ],
            ]
        );

        // Preconditions
        $storeInitialA->persist();
        $storeInitialB->persist();

        // Steps
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpenStore($storeInitialB);
        $this->editStore->getStoreForm()->selectStore($storeInitialA->getGroupId());
        $this->editStore->getFormPageActions()->save();

        return ['store' => $store];
    }
}
