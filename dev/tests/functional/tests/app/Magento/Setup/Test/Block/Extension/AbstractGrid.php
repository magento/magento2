<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Abstract Extensions Grid block.
 */
abstract class AbstractGrid extends Block
{
    /**
     * 'Next Page' button for grid.
     *
     * @var string
     */
    protected $nextPageButton = '.action-next';

    /**
     * Grid that contains the list of extensions.
     *
     * @var string
     */
    protected $dataGrid = '#installExtensionGrid';

    /**
     * Container that contains name of the extension.
     *
     * @var string
     */
    protected $extensionName = "//*[contains(text(), '%s')]";

    /**
     * Checkbox for select extension.
     *
     * @var string
     */
    protected $extensionCheckbox = "//tr[td/*[contains(text(), '%s')]]//*[contains(@ng-checked, 'selectedExtension')]";

    /**
     * Find Extension on the grid by name.
     *
     * @param Extension $extension
     * @return boolean
     */
    public function findExtensionOnGrid(Extension $extension)
    {
        $result = false;
        while (true) {
            if (($result = $this->isExtensionOnGrid($extension->getExtensionName())) || !$this->clickNextPageButton()) {
                break;
            }
        }

        return $result;
    }

    /**
     * Check that there is extension on grid.
     *
     * @param string $name
     * @return bool
     */
    protected function isExtensionOnGrid($name)
    {
        $this->waitForElementVisible($this->dataGrid);
        return $this->_rootElement->find(
            sprintf($this->extensionName, $name),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Click 'Next Page' button.
     *
     * @return bool
     */
    protected function clickNextPageButton()
    {
        $this->waitForElementVisible($this->nextPageButton);
        $nextPageButton = $this->_rootElement->find($this->nextPageButton);
        if (!$nextPageButton->isDisabled() && $nextPageButton->isVisible()) {
            $nextPageButton->click();
            return true;
        }

        return false;
    }

    /**
     * Select several extensions to install on grid.
     *
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
            sprintf($this->extensionCheckbox, $extensionName),
            Locator::SELECTOR_XPATH
        )->click();
    }
}
