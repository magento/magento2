<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\EditStore;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for UpdateStoreEntity (Store Management)
 *
 * Test Flow:
 *
 * Preconditions:
 * 1.Create store view
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Stores -> All Stores
 * 3. Open created store view
 * 4. Fill data according to dataset
 * 5. Perform all assertions
 *
 * @group Store_Management
 * @ZephyrId MAGETWO-27786
 */
class UpdateStoreEntityTest extends Injectable
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
     * Runs Update Store Entity test
     *
     * @param Store $storeInitial
     * @param Store $store
     * @return void
     */
    public function test(Store $storeInitial, Store $store)
    {
        // Preconditions:
        $storeInitial->persist();

        // Steps:
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpenStore($storeInitial);
        $this->editStore->getStoreForm()->fill($store);
        $this->editStore->getFormPageActions()->save();
    }
}
