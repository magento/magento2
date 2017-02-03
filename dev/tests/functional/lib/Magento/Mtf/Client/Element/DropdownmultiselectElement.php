<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * Typified element class for select with checkboxes.
 */
class DropdownmultiselectElement extends MultiselectElement
{
    /**
     * Selector for expanding dropdown.
     *
     * @var string
     */
    protected $toggle = 'div';

    /**
     * Selected option selector.
     *
     * @var string
     */
    protected $selectedValue = 'li._selected';

    /**
     * Option locator by value.
     *
     * @var string
     */
    protected $optionByValue = './/li[label[contains(normalize-space(.), %s)]]';

    /**
     * Set values.
     *
     * @param array|string $values
     * @return void
     */
    public function setValue($values)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        $this->find($this->toggle)->click();
        $this->deselectAll();
        $values = is_array($values) ? $values : [$values];
        foreach ($values as $value) {
            $this->find(
                sprintf($this->optionByValue, $this->escapeQuotes($value)),
                Locator::SELECTOR_XPATH
            )->click();
        }
        $this->find($this->toggle)->click();
    }

    /**
     * Get values.
     *
     * @return array
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        $values = [];
        $this->find($this->toggle)->click();
        $options = $this->getElements($this->selectedValue);
        foreach ($options as $option) {
            $values[] = $option->getText();
        }
        $this->find($this->toggle)->click();

        return $values;
    }

    /**
     * Deselect all options in the element.
     *
     * @return void
     */
    public function deselectAll()
    {
        $options = $this->getElements($this->selectedValue);
        /** @var SimpleElement $option */
        foreach ($options as $option) {
            $option->click();
        }
    }
}
