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
use Magento\Store\Test\Fixture\Website;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\EditWebsite;
use Magento\Backend\Test\Page\Adminhtml\DeleteWebsite;
use Magento\Backup\Test\Page\Adminhtml\BackupIndex;

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
        $this->deleteWebsite->getFormPageFooterActions()->delete();
    }
}
