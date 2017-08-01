<?php
/**
 * Observer model factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

/**
 * Class \Magento\Framework\Event\WrapperFactory
 *
 * @since 2.0.0
 */
class WrapperFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\Magento\Framework\Event\Observer::class, $arguments);
    }
}
