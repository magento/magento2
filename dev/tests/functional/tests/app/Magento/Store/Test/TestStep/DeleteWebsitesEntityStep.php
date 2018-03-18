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
<<<<<<< HEAD
 * Test Step for DeleteWebsitesEntity.
=======
 * Test Step for DeleteStoreEntity
>>>>>>> upstream/2.2-develop
 */
class DeleteWebsitesEntityStep implements TestStepInterface
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

    /**
<<<<<<< HEAD
=======
     * Page BackupIndex
     *
>>>>>>> upstream/2.2-develop
     * @var BackupIndex
     */
    private $backupIndex;

    /**
<<<<<<< HEAD
=======
     * Page StoreIndex
     *
>>>>>>> upstream/2.2-develop
     * @var StoreIndex
     */
    private $storeIndex;

    /**
<<<<<<< HEAD
=======
     * Page EditWebsite
     *
>>>>>>> upstream/2.2-develop
     * @var EditWebsite
     */
    private $editWebsite;

    /**
<<<<<<< HEAD
=======
     * Page StoreDelete
     *
>>>>>>> upstream/2.2-develop
     * @var DeleteWebsite
     */
    private $deleteWebsite;

    /**
<<<<<<< HEAD
=======
     * Fixture factory.
     *
>>>>>>> upstream/2.2-develop
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
<<<<<<< HEAD
=======
     * Fixture factory.
     *
>>>>>>> upstream/2.2-develop
     * @var FixtureInterface
     */
    private $item;

    /**
     * @var string
     */
    private $createBackup;

    /**
<<<<<<< HEAD
     * Prepare pages for test.
=======
     * Prepare pages for test
>>>>>>> upstream/2.2-develop
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
