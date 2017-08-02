<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

/**
 * CatalogInventory Stock Indexer Interface
 * @api
 * @since 2.0.0
 */
interface StockInterface
{
    /**
     * Reindex all stock status data
     *
     * @return $this
     * @since 2.0.0
     */
    public function reindexAll();

    /**
     * Reindex stock status data for defined ids
     *
     * @param int|array $entityIds
     * @return $this
     * @since 2.0.0
     */
    public function reindexEntity($entityIds);

    /**
     * Set Product Type Id for indexer
     *
     * @param string $typeId
     * @return $this
     * @since 2.0.0
     */
    public function setTypeId($typeId);

    /**
     * Retrieve Product Type Id for indexer
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getTypeId();
}
