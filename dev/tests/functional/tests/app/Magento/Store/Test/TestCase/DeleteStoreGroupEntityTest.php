<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\DeleteGroup;
use Magento\Backend\Test\Page\Adminhtml\EditGroup;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backup\Test\Page\Adminhtml\BackupIndex;
use Magento\Store\Test\Fixture\StoreGroup;
use Magento\Mtf\TestCase\Injectable;
use Magento\Config\Test\TestStep\SetupConfigurationStep;

/**
 * Delete StoreGroup (Store Management)
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create store
 * 2. Delete all backups
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Stores-> All Stores
 * 3. Open created store
 * 4. Click "Delete store"
 * 5. Fill data according to dataset
 * 6. Click "Delete store"
 * 7. Perform all assertions
 *
 * @group Store_Management
 * @ZephyrId MAGETWO-27596
 */
class DeleteStoreGroupEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S3';
    /* end tags */

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
     * Page DeleteGroup
     *
     * @var DeleteGroup
     */
    protected $deleteGroup;

    /**
     * Page BackupIndex
     *
     * @var BackupIndex
     */
    protected $backupIndex;

    /**
     * Injection data
     *
     * @param StoreIndex $storeIndex
     * @param EditGroup $editGroup
     * @param DeleteGroup $deleteGroup
     * @param BackupIndex $backupIndex
     * @return void
     */
    public function __inject(
        StoreIndex $storeIndex,
        EditGroup $editGroup,
        DeleteGroup $deleteGroup,
        BackupIndex $backupIndex
    ) {
        $this->storeIndex = $storeIndex;
        $this->editGroup = $editGroup;
        $this->deleteGroup = $deleteGroup;
        $this->backupIndex = $backupIndex;
    }

    /**
     * Delete StoreGroup
     *
     * @param StoreGroup $storeGroup
     * @param string $createBackup
     * @return void
     */
    public function test(StoreGroup $storeGroup, $createBackup)
    {
        //Preconditions
        $storeGroup->persist();
        /** @var SetupConfigurationStep $enableBackupsStep */
        $enableBackupsStep = $this->objectManager->create(
            SetupConfigurationStep::class,
            ['configData' => 'enable_backups_functionality']
        );
        $enableBackupsStep->run();
        $this->backupIndex->open()
            ->getBackupGrid()
            ->massaction([], 'Delete', true, 'Select All');

        //Steps
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpenStoreGroup($storeGroup);
        $this->editGroup->getFormPageActions()->delete();
        $this->deleteGroup->getDeleteGroupForm()->fillForm(['create_backup' => $createBackup]);
        $this->deleteGroup->getFormPageActions()->delete();
    }
}
