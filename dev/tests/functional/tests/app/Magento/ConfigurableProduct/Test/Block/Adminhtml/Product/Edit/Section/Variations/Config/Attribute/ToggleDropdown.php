<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config\Attribute;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Class ToggleDropdown
 * Class for toggle dropdown elements.
 */
class ToggleDropdown extends SimpleElement
{
    /**
     * Selector for field value
     *
     * @var string
     */
    protected $field = './/button/span';

    /**
     * Selector for list options
     *
     * @var string
     */
    protected $listOptions = './/ul[@data-role="dropdown-menu"]';

    /**
     * Selector for search option by text
     *
     * @var string
     */
    protected $optionByText = './/ul[@data-role="dropdown-menu"]/li/a[.="%s"]';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        if ($value != $this->getValue()) {
            $value = ('Yes' == $value) ? '%' : '$';

            $this->find($this->field, Locator::SELECTOR_XPATH)->click();
            $this->waitListOptionsVisible();
            $this->find(sprintf($this->optionByText, $value), Locator::SELECTOR_XPATH)->click();
        }
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $value = $this->find($this->field, Locator::SELECTOR_XPATH)->getText();
        return ('%' == $value) ? 'Yes' : 'No';
    }

    /**
     * Wait visible list options
     *
     * @return void
     */
    protected function waitListOptionsVisible()
    {
        $browser = $this;
        $selector = $this->listOptions;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                return $browser->find($selector, Locator::SELECTOR_XPATH)->isVisible() ? true : null;
            }
        );
    }
}
