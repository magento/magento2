<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

class ObjectFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
     */
    public function get($className)
    {
        return $this->objectManager->get($className);
    }
}
