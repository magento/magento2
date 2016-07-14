<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Client\Locator;

/**
 * Extensions Grid block.
 */
class ExtensionsGrid extends AbstractGrid
{
    /**
     * @var string
     */
    protected $installButton = "//div[contains(@class, 'item-install')]//button[contains(@href,'#install-extension-grid')]";

    /**
     * @var string
     */
    protected $popupLoading = '.popup popup-loading';

    /**
     * Click to 'Install' button.
     *
     * @return void
     */
    public function clickInstallButton()
    {
        $this->waitForElementNotVisible($this->popupLoading);
        $this->waitForElementVisible($this->installButton, Locator::SELECTOR_XPATH);
        $this->_rootElement->find($this->installButton, Locator::SELECTOR_XPATH)->click();
    }
}
