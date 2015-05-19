<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * Typified element class for option group selectors.
 */
class OptgroupselectElement extends SelectElement
{
    /**
     * Option group selector.
     *
     * @var string
     */
    protected $optGroup = 'optgroup[option[contains(.,"%s")]]';

    /**
     * Option group locator.
     *
     * @var string
     */
    protected $optionGroupValue = ".//optgroup[@label = '%s']/option[text() = '%s']";

    /**
     * Get the value of form element.
     *
     * @return string
     * @throws \Exception
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [(string)$this->getAbsoluteSelector()]);

        $selectedLabel = parent::getValue();
        if ($selectedLabel == '') {
            throw new \Exception('Selected value has not been found in optgroup select.');
        }

        $element = $this->find(sprintf($this->optGroup, $selectedLabel), Locator::SELECTOR_XPATH);
        $value = trim($element->getAttribute('label'), chr(0xC2) . chr(0xA0));
        $value .= '/' . $selectedLabel;

        return $value;
    }

    /**
     * Select value in dropdown which has option groups.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        list($group, $option) = explode('/', $value);
        $xpath = sprintf($this->optionGroupValue, $group, $option);
        $option = $this->find($xpath, Locator::SELECTOR_XPATH);
        $option->click();
    }
}
