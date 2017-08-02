<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Export adapter factory
 */
namespace Magento\ImportExport\Model\Export\Adapter;

/**
 * Class \Magento\ImportExport\Model\Export\Adapter\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * Object Manager
     *
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
     * @param string $className
     * @return \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create($className)
    {
        if (!$className) {
            throw new \InvalidArgumentException('Incorrect class name');
        }

        return $this->_objectManager->create($className);
    }
}
