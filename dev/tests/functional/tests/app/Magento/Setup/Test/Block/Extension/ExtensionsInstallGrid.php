<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Client\Locator;

/**
 * Extensions Install Grid.
 */
class ExtensionsInstallGrid extends AbstractGrid
{
    /**
     * @var string
     */
    protected $extensionInstall = "//table[contains(@class, 'data-grid')]//tr//td//span[contains(text(), '#extensionName#')]//..//..//td//div[contains(@class, 'action-wrap')]//button";

    /**
     * @var string
     */
    protected $extensionSelectVersion = "//table[contains(@class, 'data-grid')]//tr//td//span[contains(text(), '#extensionName#')]//..//..//td//span[contains(@class, 'data-grid-data')]//select";

    /**
     * Click to Install extension.
     *
     * @param string $name
     * @return void
     */
    public function clickInstall($name)
    {
        $this->_rootElement->find(
            str_replace('#extensionName#', $name, $this->extensionInstall),
            Locator::SELECTOR_XPATH
        )->click();
    }

    /**
     * Choose version of extension to install.
     *
     * @param string $name
     * @param string $version
     * @return void
     */
    public function chooseExtensionVersion($name, $version)
    {
        $select = $this->_rootElement->find(
            str_replace('#extensionName#', $name, $this->extensionSelectVersion),
            Locator::SELECTOR_XPATH,
            'strictselect'
        );

        if ($select->isVisible()) {
            $select->setValue($version);
        }
    }
}
