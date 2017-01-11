<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Updater Extension block for installing, updating and uninstalling of extensions.
 */
class Updater extends Block
{
    /**
     * Container with a message about installation, updating or uninstalling.
     *
     * @var string
     */
    protected $message = 'start-updater';

    /**
     * "Install" button that starts an installation.
     *
     * @var string
     */
    protected $button = "[ng-click*='update']";

    /**
     * Click to 'Install'|'Update'|'Uninstall' button.
     *
     * @return void
     */
    public function clickStartButton()
    {
        $this->_rootElement->find($this->button, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_rootElement->find($this->message, Locator::SELECTOR_NAME)->getText();
    }
}
