<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Install Extension block.
 */
class InstallExtension extends Block
{
    /**
     * @var string
     */
    protected $installMessage = 'start-updater';

    /**
     * @var string
     */
    protected $installButton = "[ng-click*='update']";

    /**
     * Click to 'Install' button
     *
     * @return void
     */
    public function clickInstallButton()
    {
        $this->_rootElement->find($this->installButton, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get install message.
     *
     * @return string
     */
    public function getInstallMessage()
    {
        return $this->_rootElement->find($this->installMessage, Locator::SELECTOR_NAME)->getText();
    }
}
