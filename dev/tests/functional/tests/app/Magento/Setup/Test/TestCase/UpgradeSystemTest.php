<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Setup\Test\Fixture\Upgrade;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;
use Magento\Setup\Test\Constraint\AssertVersionAndEditionCheck;
use Magento\Setup\Test\Constraint\AssertSuccessMessage;
use Magento\Setup\Test\Constraint\AssertApplicationVersion;

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
     * @var \Magento\Analytics\Mtf\App\State\NotificationTimeHandler
     */
    private $analyticsNotificationHandler;

    /**
     * @var \Magento\Mtf\Util\Iterator\ApplicationState
     */
    private $applicationStateIterator;

    /**
     * @param Dashboard $adminDashboard
     * @param SetupWizard $setupWizard
     * @param \Magento\Analytics\Mtf\App\State\NotificationTimeHandler $analyticsNotificationHandler
     * @param \Magento\Mtf\Util\Iterator\ApplicationState $applicationStateIterator
     */
    public function __inject(
        Dashboard $adminDashboard,
        SetupWizard $setupWizard,
        \Magento\Analytics\Mtf\App\State\NotificationTimeHandler $analyticsNotificationHandler,
        \Magento\Mtf\Util\Iterator\ApplicationState $applicationStateIterator
    ) {
        $this->adminDashboard = $adminDashboard;
        $this->setupWizard = $setupWizard;
        $this->analyticsNotificationHandler = $analyticsNotificationHandler;
        $this->applicationStateIterator = $applicationStateIterator;
    }

    /**
     * @param FixtureFactory $fixtureFactory
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertVersionAndEditionCheck $assertVersionAndEdition
     * @param AssertSuccessMessage $assertSuccessMessage
     * @param AssertApplicationVersion $assertApplicationVersion
     * @param array $upgrade
     * @return void
     */
    public function test(
        FixtureFactory $fixtureFactory,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertVersionAndEditionCheck $assertVersionAndEdition,
        AssertSuccessMessage $assertSuccessMessage,
        AssertApplicationVersion $assertApplicationVersion,
        array $upgrade = []
    ) {
        // Create fixture
        $upgradeFixture = $fixtureFactory->create(Upgrade::class, ['data' => $upgrade]);
        $createBackupConfig = array_intersect_key(
            $upgrade,
            ['optionsCode' => '', 'optionsMedia' => '', 'optionsDb' => '']
        );
        $createBackupFixture = $fixtureFactory->create(
            Upgrade::class,
            ['data' => $createBackupConfig]
        );
        $version = $upgrade['upgradeVersion'];

        $suffix = "( (CE|EE|B2B))$";
        $normalVersion = '(0|[1-9]\d*)';
        $preReleaseVersion = "((0(?!\\d+(\\.|\\+|{$suffix}))|[1-9A-Za-z])[0-9A-Za-z-]*)";
        $buildVersion = '([0-9A-Za-z][0-9A-Za-z-]*)';
        $versionPattern = "/^{$normalVersion}(\\.{$normalVersion}){2}"
            . "(-{$preReleaseVersion}(\\.{$preReleaseVersion})*)?"
            . "(\\+{$buildVersion}(\\.{$buildVersion})*)?{$suffix}/";

        if (preg_match($versionPattern, $version)) {
            preg_match("/(.*){$suffix}/", $version, $matches);
            $version = $matches[1];
        } else {
            $this->fail(
                "Provided version format does not comply with semantic versioning specification. Got '{$version}'"
            );
        }

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
        $this->setupWizard->getSetupHome()->clickSystemUpgrade();
        $this->setupWizard->getSelectVersion()->fill($upgradeFixture);
        if ($upgrade['otherComponents'] === 'Yes') {
            $this->setupWizard->getSelectVersion()->chooseUpgradeOtherComponents();
        }
        $this->setupWizard->getSelectVersion()->clickNext();

        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($createBackupFixture);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Check info and press 'Upgrade' button
        $assertVersionAndEdition->processAssert($this->setupWizard, $upgrade['package'], $version);
        $this->setupWizard->getSystemUpgrade()->clickSystemUpgrade();

        $assertSuccessMessage->processAssert($this->setupWizard, $upgrade['package']);

        // Disable promotion popup for Analytics module
        $appStateMetadata = $this->applicationStateIterator->current();
        $appState = \Magento\Mtf\ObjectManager::getInstance()->get($appStateMetadata['class']);
        $this->analyticsNotificationHandler->execute($appState);

        // Check application version
        $this->adminDashboard->open();
        $assertApplicationVersion->processAssert($this->adminDashboard, $version);
    }
}
