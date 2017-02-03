<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Catalog product custom attribute element.
 */
class CustomAttribute extends SimpleElement
{
    /**
     * Attribute input selector;
     *
     * @var string
     */
    protected $inputSelector = '.control [name]:not([type="hidden"]), table';

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
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        $element = $this->getElementByClass($this->getElementClass());
        $value = is_array($data) ? $data['value'] : $data;
        if ($value !== null) {
            $this->find($this->inputSelector, Locator::SELECTOR_CSS, $element)->setValue($value);
        }
    }

    /**
     * Get custom attribute value.
     *
     * @return string|array
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [__METHOD__, $this->getAbsoluteSelector()]);
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
        return $this->find($this->inputSelector, Locator::SELECTOR_CSS)->getAttribute('class');
    }
}
