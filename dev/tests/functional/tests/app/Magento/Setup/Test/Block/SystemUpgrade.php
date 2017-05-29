<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * System Upgrade block.
 */
class SystemUpgrade extends Block
{
    /**
     * @var string
     */
    protected $systemUpgradeMessage = 'start-updater';

    /**
     * 'Upgrade' button.
     *
     * @var string
     */
    protected $upgrade = "[ng-click*='update']";

    /**
     * Get upgrade message.
     *
     * @return string
     */
    public function getUpgradeMessage()
    {
        return $this->_rootElement->find($this->systemUpgradeMessage, Locator::SELECTOR_NAME)->getText();
    }

    /**
     * Click on 'Upgrade' button.
     *
     * @return void
     */
    public function clickSystemUpgrade()
    {
        $this->_rootElement->find($this->upgrade, Locator::SELECTOR_CSS)->click();
    }
}
