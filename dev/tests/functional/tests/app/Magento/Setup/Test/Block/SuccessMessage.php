<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Success Message block.
 */
class SuccessMessage extends Block
{
    /**
     * Success message block class.
     *
     * @var string
     */
    protected $successMessage = 'content-success';

    /**
     * Retrieve Updater Status.
     *
     * @return string
     */
    public function getUpdaterStatus()
    {
        $this->waitForElementVisible($this->successMessage, Locator::SELECTOR_CLASS_NAME);

        return $this->_rootElement->find($this->successMessage, Locator::SELECTOR_CLASS_NAME)->getText();
    }

    /**
     * Retrieve status of Module.
     *
     * @return array|string
     */
    public function getDisableModuleStatus()
    {
        $this->waitForElementVisible($this->successMessage, Locator::SELECTOR_CLASS_NAME);

        return $this->_rootElement->find($this->successMessage, Locator::SELECTOR_CLASS_NAME)->getText();
    }

    /**
     * Click Back to Setup button.
     *
     * @return void
     */
    public function clickBackToSetup()
    {
        $this->_rootElement->find('btn-prime', Locator::SELECTOR_CLASS_NAME)->click();
    }
}
