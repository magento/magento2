<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;

/**
 * Class ToggleDropdown
 * Class for toggle dropdown elements.
 */
class ToggleDropdown extends Element
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
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

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
        $this->_eventManager->dispatchEvent(['get_value'], [(string)$this->_locator]);

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
