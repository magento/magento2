<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Readiness block.
 */
class Readiness extends Block
{
    /**
     * 'Start Readiness Check' button.
     *
     * @var string
     */
    protected $readinessCheck = "[ng-click*='state.go']";

    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='next']";

    /**
     * 'Completed!' message.
     * [ng-switch-when="true"]
     * @var string
     */
    protected $completedMessage = '[ng-switch-when="true"]';

    /**
     * Updater application successful check.
     *
     * @var string
     */
    protected $updaterApplicationCheck = '#updater-application';

    /**
     * Cron script successful check.
     *
     * @var string
     */
    protected $cronScriptCheck = '#cron-script';

    /**
     * Dependency successful check.
     *
     * @var string
     */
    protected $dependencyCheck = '#component-dependency';

    /**
     * PHP Version successful check.
     *
     * @var string
     */
    protected $phpVersionCheck = '#php-version';

    /**
     * PHP Settings successful check.
     *
     * @var string
     */
    protected $phpSettingsCheck = '#php-settings';

    /**
     * PHP Extensions successful check.
     *
     * @var string
     */
    protected $phpExtensionCheck = '#php-extensions';

    /**
     * Click on 'Start Readiness Check' button.
     *
     * @return void
     */
    public function clickReadinessCheck()
    {
        $this->_rootElement->find($this->readinessCheck, Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->completedMessage, Locator::SELECTOR_CSS);
    }

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get Updater application check result.
     *
     * @return string
     */
    public function getUpdaterApplicationCheck()
    {
        return $this->_rootElement->find($this->updaterApplicationCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get cron script check result.
     *
     * @return string
     */
    public function getCronScriptCheck()
    {
        return $this->_rootElement->find($this->cronScriptCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get dependency check result.
     *
     * @return string
     */
    public function getDependencyCheck()
    {
        return $this->_rootElement->find($this->dependencyCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get PHP Version check result.
     *
     * @return string
     */
    public function getPhpVersionCheck()
    {
        return $this->_rootElement->find($this->phpVersionCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get setting check result.
     *
     * @return string
     */
    public function getSettingsCheck()
    {
        return $this->_rootElement->find($this->phpSettingsCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get PHP Extensions check result.
     *
     * @return string
     */
    public function getPhpExtensionsCheck()
    {
        return $this->_rootElement->find($this->phpExtensionCheck, Locator::SELECTOR_CSS)->getText();
    }
}
