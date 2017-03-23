<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\NewGroupIndex;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\StoreGroup;
use Magento\Mtf\TestCase\Injectable;

/**
 * Create New StoreGroup (Store Management)
 *
 * Test Flow:
 * 1. Open Backend
 * 2. Go to Stores-> All Stores
 * 3. Click "Create Store" button
 * 4. Fill data according to dataset
 * 5. Click "Save Store" button
 * 6. Perform all assertions
 *
 * @group Store_Management
 * @ZephyrId MAGETWO-27345
 */
class CreateStoreGroupEntityTest extends Injectable
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
     * NewGroupIndex page
     *
     * @var NewGroupIndex
     */
    protected $newGroupIndex;

    /**
     * Injection data
     *
     * @param StoreIndex $storeIndex
     * @param NewGroupIndex $newGroupIndex
     * @return void
     */
    public function __inject(
        StoreIndex $storeIndex,
        NewGroupIndex $newGroupIndex
    ) {
        $this->storeIndex = $storeIndex;
        $this->newGroupIndex = $newGroupIndex;
    }

    /**
     * Create New StoreGroup
     *
     * @param StoreGroup $storeGroup
     * @return void
     */
    public function test(StoreGroup $storeGroup)
    {
        //Steps
        $this->storeIndex->open();
        $this->storeIndex->getGridPageActions()->createStoreGroup();
        $this->newGroupIndex->getEditFormGroup()->fill($storeGroup);
        $this->newGroupIndex->getFormPageActions()->save();
    }
}
