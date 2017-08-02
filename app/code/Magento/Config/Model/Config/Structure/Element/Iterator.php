<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element;

/**
 * @api
 * @since 2.0.0
 */
class Iterator implements \Iterator
{
    /**
     * List of element data
     *
     * @var \Magento\Config\Model\Config\Structure\ElementInterface[]
     * @since 2.0.0
     */
    protected $_elements;

    /**
     * Config structure element flyweight
     *
     * @var \Magento\Config\Model\Config\Structure\AbstractElement
     * @since 2.0.0
     */
    protected $_flyweight;

    /**
     * Configuration scope
     *
     * @var string
     * @since 2.0.0
     */
    protected $_scope;

    /**
     * Last element id
     *
     * @var string
     * @since 2.0.0
     */
    protected $_lastId;

    /**
     * @param \Magento\Config\Model\Config\Structure\AbstractElement $element
     * @since 2.0.0
     */
    public function __construct(\Magento\Config\Model\Config\Structure\AbstractElement $element)
    {
        $this->_flyweight = $element;
    }

    /**
     * Set element data
     *
     * @param array $elements
     * @param string $scope
     * @return void
     * @since 2.0.0
     */
    public function setElements(array $elements, $scope)
    {
        $this->_elements = $elements;
        $this->_scope = $scope;
        if (count($elements)) {
            $lastElement = end($elements);
            $this->_lastId = $lastElement['id'];
        }
    }

    /**
     * Return the current element
     *
     * @return \Magento\Config\Model\Config\Structure\ElementInterface
     * @since 2.0.0
     */
    public function current()
    {
        return $this->_flyweight;
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
     * @since 2.0.0
     */
    public function next()
    {
        next($this->_elements);
        if (current($this->_elements)) {
            $this->_initFlyweight(current($this->_elements));
            if (!$this->current()->isVisible()) {
                $this->next();
            }
        }
    }

    /**
     * Initialize current flyweight
     *
     * @param array $element
     * @return void
     * @since 2.0.0
     */
    protected function _initFlyweight(array $element)
    {
        $this->_flyweight->setData($element, $this->_scope);
    }

    /**
     * Return the key of the current element
     *
     * @return void
     * @since 2.0.0
     */
    public function key()
    {
        key($this->_elements);
    }

    /**
     * Checks if current position is valid
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 2.0.0
     */
    public function valid()
    {
        return (bool)current($this->_elements);
    }

    /**
     * Rewind the \Iterator to the first element
     *
     * @return void Any returned value is ignored.
     * @since 2.0.0
     */
    public function rewind()
    {
        reset($this->_elements);
        if (current($this->_elements)) {
            $this->_initFlyweight(current($this->_elements));
            if (!$this->current()->isVisible()) {
                $this->next();
            }
        }
    }

    /**
     * Check whether element is last in list
     *
     * @param \Magento\Config\Model\Config\Structure\ElementInterface $element
     * @return bool
     * @since 2.0.0
     */
    public function isLast(\Magento\Config\Model\Config\Structure\ElementInterface $element)
    {
        return $element->getId() == $this->_lastId;
    }
}
