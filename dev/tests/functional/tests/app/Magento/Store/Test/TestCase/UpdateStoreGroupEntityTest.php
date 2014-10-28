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
use Magento\Store\Test\Fixture\StoreGroup;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\EditGroup;

/**
 * Update StoreGroup (Store Management)
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create store
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Stores-> All Stores
 * 3. Open created store
 * 4. Fill data according to dataset
 * 5. Click "Save Store" button
 * 6. Perform all assertions
 *
 * @group Store_Management_(PS)
 * @ZephyrId MAGETWO-27568
 */
class UpdateStoreGroupEntityTest extends Injectable
{
    /**
     * Page StoreIndex
     *
     * @var StoreIndex
     */
    protected $storeIndex;

    /**
     * Page EditGroup
     *
     * @var EditGroup
     */
    protected $editGroup;

    /**
     * Injection data
     *
     * @param StoreIndex $storeIndex
     * @param EditGroup $editGroup
     * @return void
     */
    public function __inject(
        StoreIndex $storeIndex,
        EditGroup $editGroup
    ) {
        $this->storeIndex = $storeIndex;
        $this->editGroup = $editGroup;
    }

    /**
     * Update New StoreGroup
     *
     * @param StoreGroup $storeGroupOrigin
     * @param StoreGroup $storeGroup
     * @return void
     */
    public function test(StoreGroup $storeGroupOrigin, StoreGroup $storeGroup)
    {
        //Preconditions
        $storeGroupOrigin->persist();

        //Steps
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpenStoreGroup($storeGroupOrigin);
        $this->editGroup->getEditFormGroup()->fill($storeGroup);
        $this->editGroup->getFormPageActions()->save();
    }
}
