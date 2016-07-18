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
     * Button that opens grid with installed extensions.
     *
     * @var string
     */
    protected $extensionManager = '.setup-home-item-extension';

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
    public function clickExtensionManager()
    {
        $this->_rootElement->find($this->extensionManager, Locator::SELECTOR_CSS)->click();
    }
}
