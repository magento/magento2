<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\ElementInterface;

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
    protected $optGroupValue = ".//optgroup[@label = '%s']/option[text() = '%s']";

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
        $value = $this->getData($element);
        $value .= '/' . $selectedLabel;

        return $value;
    }

    /**
     * Get element data.
     *
     * @param ElementInterface $element
     * @return string
     */
    protected function getData(ElementInterface $element)
    {
        return trim($element->getAttribute('label'), chr(0xC2) . chr(0xA0));
    }

    /**
     * Select value in dropdown which has option groups.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $option = $this->prepareSetValue($value);
        $option->click();
    }

    /**
     * Prepare setValue.
     *
     * @param string $value
     * @return ElementInterface
     */
    protected function prepareSetValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        list($group, $option) = explode('/', $value);
        $xpath = sprintf($this->optGroupValue, $group, $option);
        $option = $this->find($xpath, Locator::SELECTOR_XPATH);
        return $option;
    }
}
