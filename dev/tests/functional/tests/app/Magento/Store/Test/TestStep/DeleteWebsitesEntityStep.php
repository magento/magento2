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
<<<<<<< HEAD
=======
use Magento\Config\Test\TestStep\SetupConfigurationStep;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
<<<<<<< HEAD

/**
 * Test Step for DeleteStoreEntity
=======
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Test Step for DeleteWebsitesEntity.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class DeleteWebsitesEntityStep implements TestStepInterface
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

    /**
<<<<<<< HEAD
     * Page BackupIndex
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var BackupIndex
     */
    private $backupIndex;

    /**
<<<<<<< HEAD
     * Page StoreIndex
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var StoreIndex
     */
    private $storeIndex;

    /**
<<<<<<< HEAD
     * Page EditWebsite
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var EditWebsite
     */
    private $editWebsite;

    /**
<<<<<<< HEAD
     * Page StoreDelete
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var DeleteWebsite
     */
    private $deleteWebsite;

    /**
<<<<<<< HEAD
     * Fixture factory.
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
<<<<<<< HEAD
     * Fixture factory.
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var FixtureInterface
     */
    private $item;

    /**
     * @var string
     */
    private $createBackup;

    /**
<<<<<<< HEAD
     * Prepare pages for test
=======
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Prepare pages for test.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @param BackupIndex $backupIndex
     * @param StoreIndex $storeIndex
     * @param EditWebsite $editWebsite
     * @param DeleteWebsite $deleteWebsite
     * @param FixtureFactory $fixtureFactory
     * @param FixtureInterface $item
<<<<<<< HEAD
=======
     * @param TestStepFactory $testStepFactory
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param string $createBackup
     */
    public function __construct(
        BackupIndex $backupIndex,
        StoreIndex $storeIndex,
        EditWebsite $editWebsite,
        DeleteWebsite $deleteWebsite,
        FixtureFactory $fixtureFactory,
        FixtureInterface $item,
<<<<<<< HEAD
=======
        TestStepFactory $testStepFactory,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $createBackup = 'No'
    ) {
        $this->storeIndex = $storeIndex;
        $this->editWebsite = $editWebsite;
        $this->backupIndex = $backupIndex;
        $this->deleteWebsite = $deleteWebsite;
        $this->item = $item;
        $this->createBackup = $createBackup;
        $this->fixtureFactory = $fixtureFactory;
<<<<<<< HEAD
=======
        $this->stepFactory = $testStepFactory;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * Delete specific Store View.
     *
     * @return void
     */
    public function run()
    {
<<<<<<< HEAD
=======
        /** @var SetupConfigurationStep $enableBackupsStep */
        $enableBackupsStep = $this->stepFactory->create(
            SetupConfigurationStep::class,
            ['configData' => 'enable_backups_functionality']
        );
        $enableBackupsStep->run();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
