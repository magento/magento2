<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element;

/**
 * @api
 * @since 100.0.2
 */
class Iterator implements \Iterator
{
    /**
     * List of element data
     *
     * @var \Magento\Config\Model\Config\StructureElementInterface[]
     */
    protected $_elements;

    /**
     * Config structure element flyweight
     *
     * @var \Magento\Config\Model\Config\Structure\AbstractElement
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
     * @var \Psr\Log\LoggerInterface|null
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\State|null
     */
    protected $_state;

    /**
     * @param \Magento\Config\Model\Config\Structure\AbstractElement $element
     * @param \Psr\Log\LoggerInterface|null                          $logger
     * @param \Magento\Framework\App\State|null                      $state
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure\AbstractElement $element,
        \Psr\Log\LoggerInterface $logger = null,
        \Magento\Framework\App\State $state = null
    )
    {
        $this->_flyweight = $element;
        $this->_logger = $logger;
        $this->_state = $state;
    }

    /**
     * Set element data
     *
     * @param array $elements
     * @param string $scope
     * @return void
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function setElements(array $elements, $scope)
    {
        $this->_elements = $elements;
        $this->_scope = $scope;
        if (count($elements)) {
            $lastElement = end($elements);
            $keys = array_keys($elements);
            $elementKey = end($keys);
            if (!isset($lastElement['id'])) {
                if ($this->_logger) {
                    $this->_logger->error("Invalid module adminhtml/system.xml config for element with key '$elementKey'.");
                }
                if ($this->_state->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                    throw new \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException("Invalid configuration element defined for element with key '$elementKey'");
                }
            }
            $this->_lastId = $lastElement['id'];
        }
    }

    /**
     * Return the current element
     *
     * @return \Magento\Config\Model\Config\StructureElementInterface
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
     * @param \Magento\Config\Model\Config\Structure\ElementInterface $element
     * @return bool
     */
    public function isLast(\Magento\Config\Model\Config\Structure\ElementInterface $element)
    {
        return $element->getId() == $this->_lastId;
    }
}
