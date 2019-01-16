<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @return \Magento\Framework\Data\OptionSourceInterface
     */
    public function get($model)
    {
        $modelInstance = $this->_objectManager->get($model);
        if (false == $modelInstance instanceof \Magento\Framework\Data\OptionSourceInterface) {
            throw new \InvalidArgumentException($model
                . 'doesn\'t implement \Magento\Framework\Data\OptionSourceInterface');
        }
        return $modelInstance;
    }
}
