<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Column;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Abstract class for form, column and fieldset
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AbstractForm extends \Magento\Framework\DataObject
{
    /**
     * Form level elements collection
     *
     * @var Collection
     */
    protected $_elements;

    /**
     * Element type classes
     *
     * @var array
     */
    protected $_types = [];

    /**
     * @var Factory
     */
    protected $_factoryElement;

    /**
     * @var CollectionFactory
     */
    protected $_factoryCollection;

    /**
     * @var array
     */
    protected $customAttributes = [];

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param array $data
     */
    public function __construct(Factory $factoryElement, CollectionFactory $factoryCollection, $data = [])
    {
        $this->_factoryElement = $factoryElement;
        $this->_factoryCollection = $factoryCollection;
        parent::__construct($data);
        $this->_construct();
    }

    /**
     * Internal constructor, that is called from real constructor
     *
     * Please override this one instead of overriding real __construct constructor
     *
     * @return void
     */
    protected function _construct()
    {
    }

    /**
     * Add element type
     *
     * @param string $type
     * @param string $className
     * @return $this
     */
    public function addType($type, $className)
    {
        $this->_types[$type] = $className;
        return $this;
    }

    /**
     * Get elements collection
     *
     * @return Collection
     */
    public function getElements()
    {
        if (empty($this->_elements)) {
            $this->_elements = $this->_factoryCollection->create(['container' => $this]);
        }
        return $this->_elements;
    }

    /**
     * Disable elements
     *
     * @param boolean $readonly
     * @param boolean $useDisabled
     * @return $this
     */
    public function setReadonly($readonly, $useDisabled = false)
    {
        if ($useDisabled) {
            $this->setDisabled($readonly);
            $this->setData('readonly_disabled', $readonly);
        } else {
            $this->setData('readonly', $readonly);
        }
        foreach ($this->getElements() as $element) {
            $element->setReadonly($readonly, $useDisabled);
        }

        return $this;
    }

    /**
     * Add form element
     *
     * @param AbstractElement $element
     * @param bool|string|null $after
     * @return $this
     */
    public function addElement(AbstractElement $element, $after = null)
    {
        $element->setForm($this);
        $this->getElements()->add($element, $after);
        return $this;
    }

    /**
     * Add child element
     *
     * if $after parameter is false - then element adds to end of collection
     * if $after parameter is null - then element adds to befin of collection
     * if $after parameter is string - then element adds after of the element with some id
     *
     * @param   string $elementId
     * @param   string $type
     * @param   array  $config
     * @param   bool|string|null  $after
     * @return AbstractElement
     */
    public function addField($elementId, $type, $config, $after = false)
    {
        if (isset($this->_types[$type])) {
            $type = $this->_types[$type];
        }
        $element = $this->_factoryElement->create($type, ['data' => $config]);
        $element->setId($elementId);
        $this->addElement($element, $after);
        return $element;
    }

    /**
     * Enter description here...
     *
     * @param string $elementId
     * @return $this
     */
    public function removeField($elementId)
    {
        $this->getElements()->remove($elementId);
        return $this;
    }

    /**
     * Add fieldset
     *
     * @param string $elementId
     * @param array $config
     * @param bool|string|null $after
     * @param bool $isAdvanced
     * @return Fieldset
     */
    public function addFieldset($elementId, $config, $after = false, $isAdvanced = false)
    {
        $element = $this->_factoryElement->create('fieldset', ['data' => $config]);
        $element->setId($elementId);
        $element->setAdvanced($isAdvanced);
        $this->addElement($element, $after);
        return $element;
    }

    /**
     * Add column element
     *
     * @param string $elementId
     * @param array $config
     * @return Column
     */
    public function addColumn($elementId, $config)
    {
        $element = $this->_factoryElement->create('column', ['data' => $config]);
        $element->setForm($this)->setId($elementId);
        $this->addElement($element);
        return $element;
    }

    /**
     * Convert elements to array
     *
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function convertToArray(array $arrAttributes = [])
    {
        $res = [];
        $res['config'] = $this->getData();
        $res['formElements'] = [];
        foreach ($this->getElements() as $element) {
            $res['formElements'][] = $element->toArray();
        }
        return $res;
    }

    /**
     * Add custom attribute
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addCustomAttribute($key, $value)
    {
        $this->customAttributes[$key] = $value;
        return $this;
    }

    /**
     * Convert data into string with defined keys and values
     *
     * @param array $keys
     * @param string $valueSeparator
     * @param string $fieldSeparator
     * @param string $quote
     * @return string
     */
    public function serialize($keys = [], $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        $data = [];
        if (empty($keys)) {
            $keys = array_keys($this->_data);
        }

        $customAttributes = array_filter($this->customAttributes);
        $keys = array_merge($keys, array_keys(array_diff($this->customAttributes, $customAttributes)));

        foreach ($this->_data as $key => $value) {
            if (in_array($key, $keys)) {
                $data[] = $key . $valueSeparator . $quote . $value . $quote;
            }
        }

        foreach ($customAttributes as $key => $value) {
            $data[] = $key . $valueSeparator . $quote . $value . $quote;
        }

        return implode($fieldSeparator, $data);
    }
}
