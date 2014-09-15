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
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\EditStore;
use Magento\Backup\Test\Page\Adminhtml\BackupIndex;
use Magento\Backend\Test\Page\Adminhtml\StoreDelete;
use Magento\Store\Test\Fixture\Store;

/**
 * Test Creation for DeleteStoreEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create store view
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Stores -> All Stores
 * 3. Open created store view
 * 4. Click "Delete Store View"
 * 5. Set "Create DB Backup" according to dataSet
 * 6. Click "Delete Store View"
 * 7. Perform all assertions
 *
 * @group Store_Management_(PS)
 * @ZephyrId MAGETWO-27942
 */
class DeleteStoreEntityTest extends Injectable
{
    /**
     * Page BackupIndex
     *
     * @var BackupIndex
     */
    protected $backupIndex;

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
     * Page StoreDelete
     *
     * @var StoreDelete
     */
    protected $storeDelete;

    /**
     * Prepare pages for test
     *
     * @param BackupIndex $backupIndex
     * @param StoreIndex $storeIndex
     * @param EditStore $editStore
     * @param StoreDelete $storeDelete
     * @return void
     */
    public function __inject(
        BackupIndex $backupIndex,
        StoreIndex $storeIndex,
        EditStore $editStore,
        StoreDelete $storeDelete
    ) {
        $this->storeIndex = $storeIndex;
        $this->editStore = $editStore;
        $this->backupIndex = $backupIndex;
        $this->storeDelete = $storeDelete;
    }

    /**
     * Run Delete Store Entity test
     *
     * @param Store $store
     * @param string $createBackup
     * @return void
     */
    public function test(Store $store, $createBackup)
    {
        // Preconditions:
        $store->persist();
        $this->backupIndex->open()->getBackupGrid()->massaction([], 'Delete', true, 'Select All');

        // Steps:
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpenStore($store);
        $this->editStore->getFormPageActions()->delete();
        $this->storeDelete->getStoreForm()->fillForm(['create_backup' => $createBackup]);
        $this->storeDelete->getFormPageFooterActions()->delete();
    }
}
