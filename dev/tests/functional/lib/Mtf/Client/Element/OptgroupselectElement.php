<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Mtf\Client\Element;

use Mtf\Client\Locator;

/**
 * Class OptgroupselectElement
 * Typified element class for option group selectors
 */
class OptgroupselectElement extends SelectElement
{
    /**
     * Option group selector
     *
     * @var string
     */
    protected $optGroup = 'optgroup[option[contains(.,"%s")]]';

    /**
     * Get the value of form element
     *
     * @return string
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [(string)$this->getAbsoluteSelector()]);
        $selectedLabel = trim(parent::getValue());
        $element = $this->find(sprintf($this->optGroup, $selectedLabel), Locator::SELECTOR_XPATH);
        $value = trim($element->getAttribute('label'), chr(0xC2) . chr(0xA0));
        $value .= '/' . $selectedLabel;
        return $value;
    }

    /**
     * Select value in dropdown which has option groups
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        list($group, $option) = explode('/', $value);
        parent::setOptionGroupValue($group, $option);
    }
}
