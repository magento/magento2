<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Option;

/**
 * Array optioned object factory
 */
class ArrayPool
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get array optioned object
     *
     * @param string $model
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\Option\ArrayInterface
     */
    public function get($model)
    {
        $modelInstance = $this->_objectManager->get($model);
        if (false == $modelInstance instanceof \Magento\Framework\Option\ArrayInterface) {
            throw new \InvalidArgumentException($model . 'doesn\'t implement \Magento\Framework\Option\ArrayInterface');
        }
        return $modelInstance;
    }
}
