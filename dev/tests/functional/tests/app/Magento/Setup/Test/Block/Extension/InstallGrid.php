<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Extensions Install Grid.
 */
class InstallGrid extends AbstractGrid
{
    /**
     * "Install" button of extension.
     *
     * @var string
     */
    protected $extensionInstall = "//tr[td/*[contains(text(), '%s')]]//*[contains(@class, 'action-wrap')]//button";

    /**
     * Select version of extension.
     *
     * @var string
     */
    protected $extensionSelectVersion = "//tr[td/*[contains(text(), '%s')]]//*[contains(@id, 'selectedVersion')]";

    /**
     * "Install All" button.
     *
     * @var string
     */
    protected $installAllButton = "[ng-click*='installAll']";

    /**
     * Install extension.
     *
     * @param Extension $extension
     * @return void
     */
    public function install(Extension $extension)
    {
        $select = $this->_rootElement->find(
            sprintf($this->extensionSelectVersion, $extension->getExtensionName()),
            Locator::SELECTOR_XPATH,
            'strictselect'
        );

        if ($select->isVisible()) {
            $select->setValue('Version ' . $extension->getVersion());
        }

        $this->_rootElement->find(
            sprintf($this->extensionInstall, $extension->getExtensionName()),
            Locator::SELECTOR_XPATH
        )->click();
    }

    /**
     * Click to "Install" button that starts installing of selected extensions.
     *
     * @return void
     */
    public function clickInstallAll()
    {
        $this->_rootElement->find($this->installAllButton, Locator::SELECTOR_CSS)->click();
    }
}
