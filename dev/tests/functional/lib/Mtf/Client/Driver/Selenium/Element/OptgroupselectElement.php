<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Driver\Selenium\Element;

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
        $this->_eventManager->dispatchEvent(['get_value'], [(string)$this->_locator]);
        $selectedLabel = trim($this->_getWrappedElement()->selectedLabel());
        $value = trim(
            $this->_getWrappedElement()->byXPath(sprintf($this->optGroup, $selectedLabel))->attribute('label'),
            chr(0xC2) . chr(0xA0)
        );
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
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        list($group, $option) = explode('/', $value);
        $optionLocator = ".//optgroup[@label='$group']/option[contains(text(), '$option')]";
        $criteria = new \PHPUnit_Extensions_Selenium2TestCase_ElementCriteria('xpath');
        $criteria->value($optionLocator);
        $this->_getWrappedElement(true)->selectOptionByCriteria($criteria);
    }
}
