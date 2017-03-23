<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

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
     * PHP Version successful check.
     *
     * @var string
     */
    protected $phpVersionCheck = '#php-version';

    /**
     * PHP Extensions successful check.
     *
     * @var string
     */
    protected $phpExtensionCheck = '#php-extensions';

    /**
     * File Permission check.
     *
     * @var string
     */
    protected $filePermissionCheck = '#php-permissions';

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
     * Get File Permissions check result.
     *
     * @return string
     */
    public function getFilePermissionCheck()
    {
        return $this->_rootElement->find($this->filePermissionCheck, Locator::SELECTOR_CSS)->getText();
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
     * Get PHP Extensions check result.
     *
     * @return string
     */
    public function getPhpExtensionsCheck()
    {
        return $this->_rootElement->find($this->phpExtensionCheck, Locator::SELECTOR_CSS)->getText();
    }
}
