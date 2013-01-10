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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Config_Structure_Element_Iterator implements Iterator
{
    /**
     * List of element data
     *
     * @var Mage_Backend_Model_Config_Structure_ElementInterface[]
     */
    protected $_elements;

    /**
     * Config structure element flyweight
     *
     * @var Mage_Backend_Model_Config_Structure_ElementAbstract
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
     * @param Mage_Backend_Model_Config_Structure_ElementAbstract $element
     */
    public function __construct(Mage_Backend_Model_Config_Structure_ElementAbstract $element)
    {
        $this->_flyweight = $element;
    }

    /**
     * Set element data
     *
     * @param array $elements
     * @param string $scope
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
     * @return Mage_Backend_Model_Config_Structure_ElementInterface
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
     */
    protected function _initFlyweight(array $element)
    {
        $this->_flyweight->setData($element, $this->_scope);
    }

    /**
     * Return the key of the current element
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        key($this->_elements);
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return (bool) current($this->_elements);
    }

    /**
     * Rewind the Iterator to the first element
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
     * @param Mage_Backend_Model_Config_Structure_ElementInterface $element
     * @return bool
     */
    public function isLast(Mage_Backend_Model_Config_Structure_ElementInterface $element)
    {
        return $element->getId() == $this->_lastId;
    }
}
