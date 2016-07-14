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
    protected $extensionInstall = "//*[contains(text(), '#extensionName#')]"
        . "//..//..//*[contains(@class, 'action-wrap')]//button";

    /**
     * Select version of extension.
     *
     * @var string
     */
    protected $extensionSelectVersion = "//*[contains(text(), '#extensionName#')]"
        . "//..//..//*[contains(@class, 'data-grid-data')]//select";

    /**
     * Install extension.
     *
     * @param Extension $extension
     * @return void
     */
    public function install(Extension $extension)
    {
        $select = $this->_rootElement->find(
            str_replace('#extensionName#', $extension->getExtension(), $this->extensionSelectVersion),
            Locator::SELECTOR_XPATH,
            'strictselect'
        );

        if ($select->isVisible()) {
            $select->setValue($extension->getVersion());
        }

        $this->_rootElement->find(
            str_replace('#extensionName#', $extension->getExtension(), $this->extensionInstall),
            Locator::SELECTOR_XPATH
        )->click();
    }
}
