<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Extensions Grid block.
 */
class Grid extends AbstractGrid
{
    /**
     * "Install" button that opens grid with extensions for installing.
     *
     * @var string
     */
    protected $installButton = "//div[contains(@class, 'item-install')]"
        . "//button[contains(@href, '#install-extension-grid')]";

    /**
     * Select action of extension on the grid.
     *
     * @var string
     */
    protected $selectAction = "//*[contains(text(), 'magento/sample-data-media')]"
        . "//..//..//*[contains(@class, 'action-select')]";

    /**
     * Uninstall action of extension.
     *
     * @var string
     */
    protected $uninstallAction = "//*[contains(text(), 'magento/sample-data-media')]"
        . "//..//..//*[contains(@ng-mousedown, 'uninstall')]";

    /**
     * Popup Loading.
     *
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

    /**
     * Click to uninstall button.
     *
     * @param Extension $extension
     * @return void
     */
    public function clickUninstallButton(Extension $extension)
    {
        $this->_rootElement->find(
            str_replace('#extensionName#', $extension->getExtension(), $this->selectAction),
            Locator::SELECTOR_XPATH
        )->click();
        $this->_rootElement->find(
            str_replace('#extensionName#', $extension->getExtension(), $this->uninstallAction),
            Locator::SELECTOR_XPATH
        )->click();
    }
}
