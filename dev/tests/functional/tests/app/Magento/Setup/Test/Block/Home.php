<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * 'Module Manager' button.
     *
     * @var string
     */
    protected $moduleManager = '.setup-home-item-module';

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
     * Click on 'Extension Manager' button.
     *
     * @return void
     */
    public function clickExtensionManager()
    {
        $this->_rootElement->find($this->extensionManager, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click on 'Module Manager' section.
     *
     * @return void
     */
    public function clickModuleManager()
    {
        $this->_rootElement->find($this->moduleManager, Locator::SELECTOR_CSS)->click();
    }
}
