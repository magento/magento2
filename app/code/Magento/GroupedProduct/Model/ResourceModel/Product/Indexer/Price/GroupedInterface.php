<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price;

/**
 * @api
 * @since 100.1.1
 */
interface GroupedInterface
{
    /**
     * Reindex for all products
     *
     * @return $this
     * @since 100.1.1
     */
    public function reindexAll();

    /**
     * Reindex for defined product(s)
     *
     * @param int|array $entityIds
     * @return $this
     * @since 100.1.1
     */
    public function reindexEntity($entityIds);
}
