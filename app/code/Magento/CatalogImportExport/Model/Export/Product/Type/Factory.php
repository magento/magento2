<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Export product type factory
 */
namespace Magento\CatalogImportExport\Model\Export\Product\Type;

/**
 * Class \Magento\CatalogImportExport\Model\Export\Product\Type\Factory
 *
 */
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
     * @return \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType
     * @throws \InvalidArgumentException
     */
    public function create($className)
    {
        if (!$className) {
            throw new \InvalidArgumentException('Incorrect class name');
        }

        return $this->_objectManager->create($className);
    }
}
