<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Search menu button selector.
     *
     * @var string
     */
    private $searchMenuButton = './/div[@id="topNav"]//a[contains(@href,"search")]';

    /**
     * Open 'Search' menu item.
     *
     * @return $this
     */
    public function openSearchMenu()
    {
        $this->waitForElementVisible($this->searchMenuButton, Locator::SELECTOR_XPATH);
        $this->_rootElement->find($this->searchMenuButton, Locator::SELECTOR_XPATH)->click();
        return $this;
    }
}
