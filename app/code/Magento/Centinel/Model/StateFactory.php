<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Factory class for Credit Card types
 */
namespace Magento\Centinel\Model;

class StateFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * State class map
     *
     * @var array
     */
    protected $_stateClassMap;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $stateClassMap - key stands for card type, value define the validator class
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $stateClassMap = [])
    {
        $this->_objectManager = $objectManager;
        $this->_stateClassMap = $stateClassMap;
    }

    /**
     * Create state object
     *
     * @param string $cardType
     * @return \Magento\Centinel\Model\AbstractState|false
     */
    public function createState($cardType)
    {
        if (!isset($this->_stateClassMap[$cardType])) {
            return false;
        }
        return $this->_objectManager->create($this->_stateClassMap[$cardType]);
    }
}
