<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Class \Magento\Framework\Api\ObjectFactory
 *
 * @since 2.0.0
 */
class ObjectFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create data object
     *
     * @param string $className
     * @param array $arguments
     * @return object
     * @since 2.0.0
     */
    public function create($className, array $arguments)
    {
        return $this->objectManager->create($className, $arguments);
    }

    /**
     * Get data object
     *
     * @param string $className
     * @return object
     * @since 2.0.0
     */
    public function get($className)
    {
        return $this->objectManager->get($className);
    }
}
