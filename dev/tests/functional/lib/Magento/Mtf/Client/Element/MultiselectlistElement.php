<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * Typified element class for  Multiple Select List elements
 */
class MultiselectlistElement extends MultiselectElement
{
    /**
     * XPath selector for finding option by its position number
     *
     * @var string
     */
    protected $optionElement = './/*[contains(@class,"mselect-list-item")][%d]/label';

    /**
     * XPath selector for checking is option checked
     *
     * @var string
     */
    protected $optionCheckedElement = './/*[contains(@class, "mselect-checked")]/following-sibling::span';

    /**
     * Option locator by value.
     *
     * @var string
     */
    protected $optionByValue = './/*[contains(@class,"mselect-list-item")]/label[contains(normalize-space(.), %s)]';

    /**
     * Select options by values in multiple select list
     *
     * @param array|string $values
     * @throws \Exception
     */
    public function setValue($values)
    {
        $options = $this->getOptions();
        $values = is_array($values) ? $values : [$values];

        foreach ($options as $option) {
            /** @var \Magento\Mtf\Client\ElementInterface $option */
            $optionText = $option->getText();
            $isChecked = $option->find($this->optionCheckedElement, Locator::SELECTOR_XPATH)->isVisible();
            $inArray = in_array($optionText, $values);
            if (($isChecked && !$inArray) || (!$isChecked && $inArray)) {
                $option->click();
            }
        }
    }

    /**
     * Method that returns array with checked options in multiple select list
     *
     * @return array|string
     */
    public function getValue()
    {
        $checkedOptions = [];
        $options = $this->getOptions();

        foreach ($options as $option) {
            /** @var \Magento\Mtf\Client\ElementInterface $option */
            $checkedOption = $option->find($this->optionCheckedElement, Locator::SELECTOR_XPATH);
            if ($checkedOption->isVisible()) {
                $checkedOptions[] = $checkedOption->getText();
            }
        }

        return $checkedOptions;
    }

    /**
     * Getting all options in multi select list
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = [];
        $counter = 1;

        $newOption = $this->find(sprintf($this->optionElement, $counter), Locator::SELECTOR_XPATH);
        while ($newOption->isVisible()) {
            $options[] = $newOption;
            $counter++;
            $newOption = $this->find(sprintf($this->optionElement, $counter), Locator::SELECTOR_XPATH);
        }

        return $options;
    }

    /**
     * Method that returns array with all options in multiple select list
     *
     * @return array
     */
    public function getAllValues()
    {
        $optionsValue = [];
        $options = $this->getOptions();

        foreach ($options as $option) {
            /** @var \Magento\Mtf\Client\ElementInterface $option */
            $optionsValue[] = $option->getText();
        }

        return $optionsValue;
    }

    /**
     * Check whether value is visible in the list.
     *
     * @param string $value
     * @return bool
     */
    public function isValueVisible($value)
    {
        $option = $this->find(sprintf($this->optionByValue, $this->escapeQuotes($value)), Locator::SELECTOR_XPATH);
        return $option->isVisible();
    }
}
