<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

/**
 * CatalogInventory Stock Indexer Interface
 * @api
 */
interface StockInterface
{
    /**
     * Reindex all stock status data
     *
     * @return $this
     */
    public function reindexAll();

    /**
     * Reindex stock status data for defined ids
     *
     * @param int|array $entityIds
     * @return $this
     */
    public function reindexEntity($entityIds);

    /**
     * Set Product Type Id for indexer
     *
     * @param string $typeId
     * @return $this
     */
    public function setTypeId($typeId);

    /**
     * Retrieve Product Type Id for indexer
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTypeId();
}
