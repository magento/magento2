<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Home block.
 */
class Home extends Block
{
    /**
     * @var string
     */
    protected $systemUpgrade = '.setup-home-item-upgrade';

    /**
     * @var string
     */
    protected $componentManager = '.setup-home-item-component';

    /**
     * Click on 'System Upgrade' button.
     *
     * @return void
     */
    public function clickSystemUpgrade()
    {
        $this->_rootElement->find($this->systemUpgrade, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click on 'Component Manager' button.
     *
     * @return void
     */
    public function clickComponentManager()
    {
        $this->_rootElement->find($this->componentManager, Locator::SELECTOR_CSS)->click();
    }
}
