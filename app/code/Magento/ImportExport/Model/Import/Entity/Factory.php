<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Import entity factory
 */
namespace Magento\ImportExport\Model\Import\Entity;

/**
 * Class \Magento\ImportExport\Model\Import\Entity\Factory
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
     * @return AbstractEntity|\Magento\ImportExport\Model\Import\AbstractEntity
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
