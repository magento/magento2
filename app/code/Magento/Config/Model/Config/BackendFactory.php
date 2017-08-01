<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;

/**
 * @api
 * @since 2.0.0
 */
class BackendFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager)
    {
        $this->_objectManager = $objectmanager;
    }

    /**
     * Create backend model by name
     *
     * @param string $modelName
     * @param array $arguments The object arguments
     * @return \Magento\Framework\App\Config\ValueInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create($modelName, array $arguments = [])
    {
        $model = $this->_objectManager->create($modelName, $arguments);
        if (!$model instanceof \Magento\Framework\App\Config\ValueInterface) {
            throw new \InvalidArgumentException('Invalid config field backend model: ' . $modelName);
        }
        return $model;
    }
}
