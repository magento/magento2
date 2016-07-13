<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Abstract Extensions Grid block.
 */
abstract class AbstractGrid extends Block
{
    /**
     * @var string
     */
    protected $nextPageButton = '.action-next';

    /**
     * @var string
     */
    protected $dataGrid = '.data-grid';

    /**
     * @var string
     */
    protected $extensionName = "//table[contains(@class, 'data-grid')]//tr//td//span[contains(text(), '#extensionName#')]";

    /**
     * Click 'Next Page' button
     *
     * @return bool
     */
    public function clickNextPageButton()
    {
        $this->waitForElementVisible($this->nextPageButton);
        $nextPageButton = $this->_rootElement->find($this->nextPageButton);
        if (!$nextPageButton->isDisabled()) {
            $nextPageButton->click();
            return true;
        }

        return false;
    }

    /**
     * Check that there is extension on grid
     *
     * @param string $name
     * @return bool
     */
    public function isExtensionOnGrid($name)
    {
        $this->waitForElementVisible($this->dataGrid);
        return $this->_rootElement->find(
            str_replace('#extensionName#', $name, $this->extensionName),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }
}
