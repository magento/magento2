<?php
/**
 * Observer model factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

/**
 * Class \Magento\Framework\Event\ObserverFactory
 *
 * @since 2.0.0
 */
class ObserverFactory
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
     * Get observer model instance
     *
     * @param string $className
     * @return mixed
     * @since 2.0.0
     */
    public function get($className)
    {
        return $this->_objectManager->get($className);
    }

    /**
     * Create observer model instance
     *
     * @param string $className
     * @param array $arguments
     * @return mixed
     * @since 2.0.0
     */
    public function create($className, array $arguments = [])
    {
        return $this->_objectManager->create($className, $arguments);
    }
}
