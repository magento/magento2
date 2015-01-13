<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;

/**
 * Catalog product custom attribute element.
 */
class CustomAttribute extends Element
{
    /**
     * Attribute input selector;
     *
     * @var string
     */
    protected $inputSelector = '.control [data-ui-id][name]';

    /**
     * Attribute class to element type reference.
     *
     * @var array
     */
    protected $classReference = [
        'input-text' => null,
        'textarea' => null,
        'hasDatepicker' => 'datepicker',
        'select' => 'select',
        'multiselect' => 'multiselect',
    ];

    /**
     * Set attribute value.
     *
     * @param array|string $data
     * @return void
     */
    public function setValue($data)
    {
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        $element = $this->getElementByClass($this->getElementClass());
        $value = is_array($data) ? $data['value'] : $data;
        $this->find($this->inputSelector, Locator::SELECTOR_CSS, $element)->setValue($value);
    }

    /**
     * Get custom attribute value.
     *
     * @return string|array
     */
    public function getValue()
    {
        $this->_eventManager->dispatchEvent(['get_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        $inputType = $this->getElementByClass($this->getElementClass());
        return $this->find($this->inputSelector, Locator::SELECTOR_CSS, $inputType)->getValue();
    }

    /**
     * Get element type by class.
     *
     * @param string $class
     * @return string
     */
    protected function getElementByClass($class)
    {
        $element = null;
        foreach ($this->classReference as $key => $reference) {
            if (strpos($class, $key) !== false) {
                $element = $reference;
            }
        }
        return $element;
    }

    /**
     * Get element class.
     *
     * @return string
     */
    protected function getElementClass()
    {
        $criteria = new \PHPUnit_Extensions_Selenium2TestCase_ElementCriteria('css selector');
        $criteria->value($this->inputSelector);
        $input = $this->_getWrappedElement()->element($criteria);
        return $input->attribute('class');
    }
}
