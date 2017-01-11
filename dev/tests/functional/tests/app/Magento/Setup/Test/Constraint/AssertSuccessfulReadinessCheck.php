<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint;

use Magento\Setup\Test\Page\Adminhtml\SetupWizard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that Updater, Dependency, PHP Version, PHP Extensions and File Permission are ok.
 */
class AssertSuccessfulReadinessCheck extends AbstractConstraint
{
    /**
     * Updater application message
     */
    const UPDATER_APPLICATION_MESSAGE = 'Updater application is available';

    /**
     * Cron script message
     */
    const CRON_SCRIPT_MESSAGE = 'Cron script readiness check passed';

    /**
     * Dependency check message
     */
    const DEPENDENCY_CHECK_MESSAGE = 'Component dependency is correct';

    /**
     * PHP version message.
     */
    const PHP_VERSION_MESSAGE = 'Your PHP version is correct';

    /**
     * PHP extensions message.
     */
    const PHP_SETTING_REGEXP = 'Your PHP settings are correct';

    /**
     * PHP extensions message.
     */
    const PHP_EXTENSIONS_REGEXP = '/You meet (\d+) out of \1 PHP extensions requirements\./';

    /**
     * Assert that readiness check items are passed.
     *
     * @param SetupWizard $setupWizard
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard)
    {
        \PHPUnit_Framework_Assert::assertContains(
            self::UPDATER_APPLICATION_MESSAGE,
            $setupWizard->getReadiness()->getUpdaterApplicationCheck(),
            'Updater application check is incorrect.'
        );
        \PHPUnit_Framework_Assert::assertContains(
            self::CRON_SCRIPT_MESSAGE,
            $setupWizard->getReadiness()->getCronScriptCheck(),
            'Cron scripts are incorrect.'
        );
        \PHPUnit_Framework_Assert::assertContains(
            self::DEPENDENCY_CHECK_MESSAGE,
            $setupWizard->getReadiness()->getDependencyCheck(),
            'Dependency check is incorrect.'
        );
        \PHPUnit_Framework_Assert::assertContains(
            self::PHP_VERSION_MESSAGE,
            $setupWizard->getReadiness()->getPhpVersionCheck(),
            'PHP version is incorrect.'
        );
        \PHPUnit_Framework_Assert::assertContains(
            self::PHP_SETTING_REGEXP,
            $setupWizard->getReadiness()->getSettingsCheck(),
            'PHP settings check failed.'
        );
        \PHPUnit_Framework_Assert::assertRegExp(
            self::PHP_EXTENSIONS_REGEXP,
            $setupWizard->getReadiness()->getPhpExtensionsCheck(),
            'PHP extensions missed.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "System Upgrade readiness check passed.";
    }
}
