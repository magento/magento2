<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\DeleteWebsite;
use Magento\Backend\Test\Page\Adminhtml\EditWebsite;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backup\Test\Page\Adminhtml\BackupIndex;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\TestCase\Injectable;

/**
 * Delete Website (Store Management)
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create website
 * 2. Delete all backups
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Stores-> All Stores
 * 3. Open created website
 * 4. Click "Delete Web Site"
 * 5. Fill data according to dataset
 * 6. Click "Delete Web Site"
 * 7. Perform all assertions
 *
 * @group Store_Management_(PS)
 * @ZephyrId MAGETWO-27723
 */
class DeleteWebsiteEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
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
     * Page DeleteWebsite
     *
     * @var DeleteWebsite
     */
    protected $deleteWebsite;

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
     * @param EditWebsite $editWebsite
     * @param DeleteWebsite $deleteWebsite
     * @param BackupIndex $backupIndex
     * @return void
     */
    public function __inject(
        StoreIndex $storeIndex,
        EditWebsite $editWebsite,
        DeleteWebsite $deleteWebsite,
        BackupIndex $backupIndex
    ) {
        $this->storeIndex = $storeIndex;
        $this->editWebsite = $editWebsite;
        $this->deleteWebsite = $deleteWebsite;
        $this->backupIndex = $backupIndex;
    }

    /**
     * Delete Website
     *
     * @param Website $website
     * @param string $createBackup
     * @return void
     */
    public function test(Website $website, $createBackup)
    {
        //Preconditions
        $website->persist();
        $this->backupIndex->open()->getBackupGrid()->massaction([], 'Delete', true, 'Select All');

        //Steps
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpenWebsite($website);
        $this->editWebsite->getFormPageActions()->delete();
        $this->deleteWebsite->getDeleteWebsiteForm()->fillForm(['create_backup' => $createBackup]);
        $this->deleteWebsite->getFormPageActions()->delete();
    }
}
