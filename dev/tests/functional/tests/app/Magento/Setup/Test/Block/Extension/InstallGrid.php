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
     * Checkbox for select extension.
     *
     * @var string
     */
    protected $extensionCheckbox = "//*[contains(text(), '#extensionName#')]"
        . "//..//..//*[contains(@ng-checked, 'selectedExtension')]";

    /**
     * Install extension.
     *
     * @param Extension $extension
     * @return void
     */
    public function install(Extension $extension)
    {
        $select = $this->_rootElement->find(
            str_replace('#extensionName#', $extension->getExtensionName(), $this->extensionSelectVersion),
            Locator::SELECTOR_XPATH,
            'strictselect'
        );

        if ($select->isVisible()) {
            $select->setValue($extension->getVersion());
        }

        $this->_rootElement->find(
            str_replace('#extensionName#', $extension->getExtensionName(), $this->extensionInstall),
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
        $this->_rootElement->find("[ng-click*='installAll']", Locator::SELECTOR_CSS)->click();
    }

    /**
     * @param Extension[] $extensions
     * @return Extension[]
     */
    public function selectSeveralExtensions(array $extensions)
    {
        while (true) {
            foreach ($extensions as $key => $extension) {
                if ($this->isExtensionOnGrid($extension->getExtensionName())) {
                    $this->selectExtension($extension->getExtensionName());
                    unset($extensions[$key]);
                }
            }

            if (empty($extensions) || !$this->clickNextPageButton()) {
                break;
            }
        }

        return $extensions;
    }

    /**
     * Select extension on grid, check checkbox.
     *
     * @param string $extensionName
     * @return void
     */
    protected function selectExtension($extensionName)
    {
        $this->_rootElement->find(
            str_replace('#extensionName#', $extensionName, $this->extensionCheckbox),
            Locator::SELECTOR_XPATH
        )->click();
    }
}
