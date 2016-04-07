<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    protected $inputSelector = '[name="product[%s]"]';

    /**
     * Attribute class to element type reference.
     *
     * @var array
     */
    protected $classReference = [
        'admin__control-text' => null,
        'textarea' => null,
        'hasDatepicker' => 'datepicker',
        'admin__control-select' => 'select',
        'admin__control-multiselect' => 'multiselect',
        'admin__actions-switch-checkbox' => 'switcher'
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
        $code = isset($data['code']) ? $data['code'] : $this->getAttributeCode($this->getAbsoluteSelector());
        $element = $this->getElementByClass($this->getElementClass($code));
        $value = is_array($data) ? $data['value'] : $data;
        if ($value !== null) {
            $this->find(sprintf($this->inputSelector, $code), Locator::SELECTOR_CSS, $element)->setValue($value);
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
        $code = $this->getAttributeCode($this->getAbsoluteSelector());
        $inputType = $this->getElementByClass($this->getElementClass($code));
        return $this->find(sprintf($this->inputSelector, $code), Locator::SELECTOR_CSS, $inputType)->getValue();
    }

    /**
     * Get element type by class.
     *
     * @param string $class
     * @return string
     */
    private function getElementByClass($class)
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
     * @param string $code
     * @return string
     */
    private function getElementClass($code)
    {
        return $this->find(sprintf($this->inputSelector, $code), Locator::SELECTOR_CSS)->getAttribute('class');
    }

    /**
     * Get attribute code.
     *
     * @param string $attributeSelector
     * @return string
     */
    private function getAttributeCode($attributeSelector)
    {
        preg_match('/data-index="(.*)"/', $attributeSelector, $matches);
        $code = !empty($matches[1]) ? $matches[1] : '';

        return $code;
    }
}
