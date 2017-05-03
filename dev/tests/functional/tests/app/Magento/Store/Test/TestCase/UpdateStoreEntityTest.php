<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\EditStore;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\TestCase\Injectable;
use Magento\Store\Test\TestStep\RestoreDefaultStoreViewStep;

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
     * Restore Default Store View step.
     *
     * @var RestoreDefaultStoreViewStep
     */
    private $restoreDefaultStoreViewStep;

    /**
     * Initial store fixture.
     *
     * @var Store
     */
    private $storeInitial;

    /**
     * Preparing pages for test
     *
     * @param StoreIndex $storeIndex
     * @param EditStore $editStore
     * @param RestoreDefaultStoreViewStep $restoreDefaultStoreViewStep
     * @return void
     */
    public function __inject(
        StoreIndex $storeIndex,
        EditStore $editStore,
        RestoreDefaultStoreViewStep $restoreDefaultStoreViewStep
    ) {
        $this->storeIndex = $storeIndex;
        $this->editStore = $editStore;
        $this->restoreDefaultStoreViewStep = $restoreDefaultStoreViewStep;
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
        $this->storeInitial = $storeInitial;
        $storeInitial->persist();

        // Steps:
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpenStore($storeInitial);
        $this->editStore->getStoreForm()->fill($store);
        $this->editStore->getFormPageActions()->save();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if ($this->storeInitial->getCode() == 'default') {
            $this->restoreDefaultStoreViewStep->run();
        }
    }
}
