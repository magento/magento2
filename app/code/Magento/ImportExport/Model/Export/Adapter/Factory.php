<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Export adapter factory
 */
namespace Magento\ImportExport\Model\Export\Adapter;

class Factory
{
    /**
     * Object manager instance.
     *
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
     * Create export adapter instance.
     *
     * @param string $className
     * @param array $arguments
     * @return \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
     * @throws \InvalidArgumentException
     */
    public function create($className, array $arguments = [])
    {
        if (!$className) {
            throw new \InvalidArgumentException('Incorrect class name');
        }

        return $this->_objectManager->create($className, $arguments);
    }
}
