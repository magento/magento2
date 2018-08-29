<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\EditWebsite;
use Magento\Backend\Test\Page\Adminhtml\DeleteWebsite;
use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backup\Test\Page\Adminhtml\BackupIndex;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Test Step for DeleteWebsitesEntity.
 */
class DeleteWebsitesEntityStep implements TestStepInterface
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * @var BackupIndex
     */
    private $backupIndex;

    /**
     * @var StoreIndex
     */
    private $storeIndex;

    /**
     * @var EditWebsite
     */
    private $editWebsite;

    /**
     * @var DeleteWebsite
     */
    private $deleteWebsite;

    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var FixtureInterface
     */
    private $item;

    /**
     * @var string
     */
    private $createBackup;

    /**
     * Prepare pages for test.
     *
     * @param BackupIndex $backupIndex
     * @param StoreIndex $storeIndex
     * @param EditWebsite $editWebsite
     * @param DeleteWebsite $deleteWebsite
     * @param FixtureFactory $fixtureFactory
     * @param FixtureInterface $item
     * @param string $createBackup
     */
    public function __construct(
        BackupIndex $backupIndex,
        StoreIndex $storeIndex,
        EditWebsite $editWebsite,
        DeleteWebsite $deleteWebsite,
        FixtureFactory $fixtureFactory,
        FixtureInterface $item,
        $createBackup = 'No'
    ) {
        $this->storeIndex = $storeIndex;
        $this->editWebsite = $editWebsite;
        $this->backupIndex = $backupIndex;
        $this->deleteWebsite = $deleteWebsite;
        $this->item = $item;
        $this->createBackup = $createBackup;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Delete specific Store View.
     *
     * @return void
     */
    public function run()
    {
        $this->backupIndex->open()->getBackupGrid()->massaction([], 'Delete', true, 'Select All');
        $this->storeIndex->open();
        $websiteNames = $this->item->getWebsiteIds();
        if (is_array($websiteNames) && count($websiteNames) > 0) {
            $websiteName = end($websiteNames);
            $this->storeIndex->getStoreGrid()->searchAndOpenWebsiteByName($websiteName);
            $this->editWebsite->getFormPageActions()->delete();
            $this->deleteWebsite->getDeleteWebsiteForm()->fillForm(['create_backup' => $this->createBackup]);
            $this->deleteWebsite->getFormPageActions()->delete();
        }
    }
}
