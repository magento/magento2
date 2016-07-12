<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Extensions Install Grid
 */
class ExtensionsInstallGrid extends Form
{
    /**
     * @var string
     */
    protected $perPageSelect = '#perPage';

    protected $nextPageButton = '.action-next';

    protected $extensionNameXpath = "//table[contains(@class, 'data-grid')]//tr//td//span[contains(text(), '#extensionName#')]";

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
    
    public function isExtensionOnGrid($name)
    {
        return $this->_rootElement->find(
            str_replace('#extensionName#', $name, $this->extensionNameXpath),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }
}
