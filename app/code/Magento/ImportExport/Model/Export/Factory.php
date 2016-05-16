<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Entity factory
 */
namespace Magento\ImportExport\Model\Export;

class Factory
{
    /**
     * Object Manager
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
     * @param string $className
     * @return \Magento\Framework\Data\Collection
     * @throws \InvalidArgumentException
     */
    public function create($className)
    {
        if (!$className) {
            throw new \InvalidArgumentException('Incorrect class name');
        }

        $attributeCollection = $this->_objectManager->create($className);

        if (!$attributeCollection instanceof \Magento\Framework\Data\Collection) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Attribute factory class \"%s\" must implement \Magento\Framework\Data\Collection.",
                    get_class($attributeCollection)
                )
            );
        }
        return $attributeCollection;
    }
}
