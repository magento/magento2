<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogInventory Stock Indexers Factory
 */
namespace Magento\CatalogInventory\Model\ResourceModel\Indexer;

/**
 * @api
 * @since 2.0.0
 */
class StockFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Default Stock Indexer resource model name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_defaultIndexer = \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock::class;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new indexer object
     *
     * @param string $indexerClassName
     * @param array $data
     * @return \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\StockInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create($indexerClassName = '', array $data = [])
    {
        if (empty($indexerClassName)) {
            $indexerClassName = $this->_defaultIndexer;
        }
        $indexer = $this->_objectManager->create($indexerClassName, $data);
        if (false == $indexer instanceof \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\StockInterface) {
            throw new \InvalidArgumentException(
                $indexerClassName .
                ' doesn\'t implement \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\StockInterface'
            );
        }
        return $indexer;
    }
}
