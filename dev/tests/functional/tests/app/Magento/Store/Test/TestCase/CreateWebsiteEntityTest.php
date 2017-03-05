<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\NewWebsiteIndex;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\TestCase\Injectable;

/**
 * Create Website (Store Management)
 *
 * Test Flow:
 * 1. Open Backend
 * 2. Go to Stores-> All Stores
 * 3. Click "Create Website" button
 * 4. Fill data according to dataset
 * 5. Click "Save Web Site" button
 * 6. Perform all assertions
 *
 * @group Store_Management
 * @ZephyrId MAGETWO-27665
 */
class CreateWebsiteEntityTest extends Injectable
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
     * NewWebsiteIndex page
     *
     * @var NewWebsiteIndex
     */
    protected $newWebsiteIndex;

    /**
     * Injection data
     *
     * @param StoreIndex $storeIndex
     * @param NewWebsiteIndex $newWebsiteIndex
     * @return void
     */
    public function __inject(
        StoreIndex $storeIndex,
        NewWebsiteIndex $newWebsiteIndex
    ) {
        $this->storeIndex = $storeIndex;
        $this->newWebsiteIndex = $newWebsiteIndex;
    }

    /**
     * Create Website
     *
     * @param Website $website
     * @return void
     */
    public function test(Website $website)
    {
        //Steps
        $this->storeIndex->open();
        $this->storeIndex->getGridPageActions()->addNew();
        $this->newWebsiteIndex->getEditWebsiteForm()->fill($website);
        $this->newWebsiteIndex->getFormPageActions()->save();
    }
}
