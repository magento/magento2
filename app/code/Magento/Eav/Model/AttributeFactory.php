<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

/**
 * EAV attribute model factory
 *
 * @api
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class AttributeFactory
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
     * Create new Eav attribute instance
     *
     * @param string $className
     * @param array $arguments
     * @return mixed
     * @since 2.0.0
     */
    public function createAttribute($className, $arguments = [])
    {
        return $this->_objectManager->create($className, ['data' => $arguments]);
    }
}
