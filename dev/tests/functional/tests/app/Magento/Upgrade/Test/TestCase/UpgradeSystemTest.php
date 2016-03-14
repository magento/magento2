<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  Magento\Upgrade\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Upgrade\Test\Page\Adminhtml\SetupWizard;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Upgrade\Test\Constraint\AssertSuccessfulReadinessCheck;

class UpgradeSystemTest extends Injectable
{
    /**
     * Page System Upgrade Index.
     *
     * @var SetupWizard
     */
    protected $setupWizard;

    /**
     * @var Dashboard
     */
    protected $adminDashboard;

    /**
     * Injection data.
     *
     * @param Dashboard $adminDashboard
     * @param SetupWizard $setupWizard
     * @return void
     */
    public function __inject(
        Dashboard $adminDashboard,
        SetupWizard $setupWizard
    ) {
        $this->adminDashboard = $adminDashboard;
        $this->setupWizard = $setupWizard;
    }

    /**
     * @param array $upgrade
     * @param FixtureFactory $fixtureFactory
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @return void
     */
    public function test(
        $upgrade = [],
        FixtureFactory $fixtureFactory,
        AssertSuccessfulReadinessCheck $assertReadiness
    ) {
        // Create fixture
        $upgradeFixture = $fixtureFactory->create('Magento\Upgrade\Test\Fixture\Upgrade', ['data' => $upgrade]);
        $createBackupConfig = array_intersect_key(
            $upgrade,
            ['optionsCode' => '', 'optionsMedia' => '', 'optionsDb' => '']
        );
        $createBackupFixture = $fixtureFactory->create(
            'Magento\Upgrade\Test\Fixture\Upgrade',
            ['data' => $createBackupConfig]
        );


        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        // Authenticate on repo.magento.com
        if ($upgrade['needAuthentication'] === 'Yes') {
            $this->setupWizard->getSystemConfig()->clickSystemConfig();
            $this->setupWizard->getAuthentication()->fill($upgradeFixture);
            $this->setupWizard->getAuthentication()->clickSaveConfig();
            $this->setupWizard->open();
        }

        // Select upgrade to version
        $this->setupWizard->getSystemUpgrade()->clickSystemUpgrade();
        $this->setupWizard->getSelectVersion()->fill($upgradeFixture);
        $this->setupWizard->getSelectVersion()->clickNext();

        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();


        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($createBackupFixture);
        $this->setupWizard->getCreateBackup()->clickNext();

        sleep(5);
    }
}
