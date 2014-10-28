<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Structure\Element;

class Iterator implements \Iterator
{
    /**
     * List of element data
     *
     * @var \Magento\Backend\Model\Config\Structure\ElementInterface[]
     */
    protected $_elements;

    /**
     * Config structure element flyweight
     *
     * @var \Magento\Backend\Model\Config\Structure\AbstractElement
     */
    protected $_flyweight;

    /**
     * Configuration scope
     *
     * @var string
     */
    protected $_scope;

    /**
     * Last element id
     *
     * @var string
     */
    protected $_lastId;

    /**
     * @param \Magento\Backend\Model\Config\Structure\AbstractElement $element
     */
    public function __construct(\Magento\Backend\Model\Config\Structure\AbstractElement $element)
    {
        $this->_flyweight = $element;
    }

    /**
     * Set element data
     *
     * @param array $elements
     * @param string $scope
     * @return void
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
     * @return \Magento\Backend\Model\Config\Structure\ElementInterface
     */
    public function current()
    {
        return $this->_flyweight;
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
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
     */
    protected function _initFlyweight(array $element)
    {
        $this->_flyweight->setData($element, $this->_scope);
    }

    /**
     * Return the key of the current element
     *
     * @return void
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
     */
    public function valid()
    {
        return (bool)current($this->_elements);
    }

    /**
     * Rewind the \Iterator to the first element
     *
     * @return void Any returned value is ignored.
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
     * @param \Magento\Backend\Model\Config\Structure\ElementInterface $element
     * @return bool
     */
    public function isLast(\Magento\Backend\Model\Config\Structure\ElementInterface $element)
    {
        return $element->getId() == $this->_lastId;
    }
}
