<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Menu block on Authorize.Net sandbox.
 */
class Menu extends Block
{
    /**
     * 'Got It' button selector.
     * This button is located in notification window which may appear immediately after login.
     *
     * @var string
     */
    private $gotItButton = '#btnGetStartedGotIt';

    /**
     * Search menu button selector.
     *
     * @var string
     */
    private $searchMenuButton = './/div[@id="topNav"]//a[contains(@href,"search")]';

    /**
     * Accept notification if it appears after login.
     *
     * @return void
     */
    public function acceptNotification()
    {
        $element = $this->browser->find($this->gotItButton);
        if ($element->isVisible()) {
            $element->click();
        }
    }

    /**
     * Open 'Search' menu item.
     *
     * @return void
     */
    public function openSearchMenu()
    {
        $this->_rootElement->find($this->searchMenuButton, Locator::SELECTOR_XPATH)->click();
    }
}
