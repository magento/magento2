<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\AbstractForm;

/**
 * Form element collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection implements \ArrayAccess, \IteratorAggregate
{
    /**
     * Elements storage
     *
     * @var array
     * @since 2.0.0
     */
    private $_elements;

    /**
     * Elements container
     *
     * @var AbstractForm
     * @since 2.0.0
     */
    private $_container;

    /**
     * Class constructor
     *
     * @param AbstractForm $container
     * @since 2.0.0
     */
    public function __construct(AbstractForm $container)
    {
        $this->_elements = [];
        $this->_container = $container;
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     * @since 2.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_elements);
    }

    /**
     * Implementation of \ArrayAccess:offsetSet()
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     * @since 2.0.0
     */
    public function offsetSet($key, $value)
    {
        $this->_elements[$key] = $value;
    }

    /**
     * Implementation of \ArrayAccess:offsetGet()
     *
     * @param mixed $key
     * @return AbstractElement
     * @since 2.0.0
     */
    public function offsetGet($key)
    {
        return $this->_elements[$key];
    }

    /**
     * Implementation of \ArrayAccess:offsetUnset()
     *
     * @param mixed $key
     * @return void
     * @since 2.0.0
     */
    public function offsetUnset($key)
    {
        unset($this->_elements[$key]);
    }

    /**
     * Implementation of \ArrayAccess:offsetExists()
     *
     * @param mixed $key
     * @return boolean
     * @since 2.0.0
     */
    public function offsetExists($key)
    {
        return isset($this->_elements[$key]);
    }

    /**
     * Add element to collection
     *
     * @todo get it straight with $after
     * @param AbstractElement $element
     * @param bool|string $after
     * @return AbstractElement
     * @since 2.0.0
     */
    public function add(AbstractElement $element, $after = false)
    {
        // Set the Form for the node
        if ($this->_container->getForm() instanceof Form) {
            $element->setContainer($this->_container);
            $element->setForm($this->_container->getForm());
        }

        if ($after === false) {
            $this->_elements[] = $element;
        } elseif ($after === '^') {
            array_unshift($this->_elements, $element);
        } elseif (is_string($after)) {
            $newOrderElements = [];
            foreach ($this->_elements as $index => $currElement) {
                if ($currElement->getId() == $after) {
                    $newOrderElements[] = $currElement;
                    $newOrderElements[] = $element;
                    $this->_elements = array_merge($newOrderElements, array_slice($this->_elements, $index + 1));
                    return $element;
                }
                $newOrderElements[] = $currElement;
            }
            $this->_elements[] = $element;
        }

        return $element;
    }

    /**
     * Sort elements by values using a user-defined comparison function
     *
     * @param mixed $callback
     * @return $this
     * @since 2.0.0
     */
    public function usort($callback)
    {
        usort($this->_elements, $callback);
        return $this;
    }

    /**
     * Remove element from collection
     *
     * @param mixed $elementId
     * @return $this
     * @since 2.0.0
     */
    public function remove($elementId)
    {
        foreach ($this->_elements as $index => $element) {
            if ($elementId == $element->getId()) {
                unset($this->_elements[$index]);
            }
        }
        // Renumber elements for further correct adding and removing other elements
        $this->_elements = array_merge($this->_elements, []);
        return $this;
    }

    /**
     * Count elements in collection
     *
     * @return int
     * @since 2.0.0
     */
    public function count()
    {
        return count($this->_elements);
    }

    /**
     * Find element by ID
     *
     * @param mixed $elementId
     * @return AbstractElement
     * @since 2.0.0
     */
    public function searchById($elementId)
    {
        foreach ($this->_elements as $element) {
            if ($element->getId() == $elementId) {
                return $element;
            }
        }
        return null;
    }
}
