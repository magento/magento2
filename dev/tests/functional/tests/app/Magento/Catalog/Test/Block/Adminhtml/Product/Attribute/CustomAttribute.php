<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute;

use Magento\Mtf\Client\DriverInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\System\Event\EventManagerInterface;

/**
 * Catalog product custom attribute element.
 */
class CustomAttribute extends SimpleElement
{
    /**
     * Attribute input selector.
     *
     * @var string
     */
    private $inputSelector = '[name="product[%s]"]';

    /**
     * Locator for data grid.
     *
     * @var string
     */
    private $dataGrid = '[data-role="grid"]';

    /**
     * Attribute class to element type reference.
     *
     * @var array
     */
    private $classReferences = [];

    /**
     * Constructor
     *
     * @param DriverInterface $driver
     * @param EventManagerInterface $eventManager
     * @param Locator $locator
     * @param ElementInterface $context
     * @param array $classReferences
     */
    public function __construct(
        DriverInterface $driver,
        EventManagerInterface $eventManager,
        Locator $locator,
        ElementInterface $context,
        array $classReferences
    ) {
        parent::__construct($driver, $eventManager, $locator, $context);
        $this->classReferences = $classReferences;
    }

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
            $this->find(
                str_replace('%code%', $code, $element['selector']),
                Locator::SELECTOR_CSS,
                $element['type']
            )->setValue($value);
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
     * Get element by class.
     *
     * @param string $class
     * @return array|null
     */
    private function getElementByClass($class)
    {
        $element = null;
        foreach (array_keys($this->classReferences) as $key) {
            if ($class == $key) {
                return $this->classReferences[$class];
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
        return $this->find($this->dataGrid)->isVisible()
            ? 'dynamicRows'
            : $this->find(sprintf($this->inputSelector, $code), Locator::SELECTOR_CSS)->getAttribute('class');
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
