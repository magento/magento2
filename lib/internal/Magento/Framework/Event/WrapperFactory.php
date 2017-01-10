<?php
/**
 * Observer model factory
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

class WrapperFactory
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
     * Create wrapper instance
     *
     * @param array $arguments
     * @return \Magento\Framework\Event\Observer
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\Magento\Framework\Event\Observer::class, $arguments);
    }
}
