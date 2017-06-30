<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price;

/**
 * @api
 */
interface GroupedInterface
{
    /**
     * Reindex for all products
     *
     * @return $this
     */
    public function reindexAll();

    /**
     * Reindex for defined product(s)
     *
     * @param int|array $entityIds
     * @return $this
     */
    public function reindexEntity($entityIds);
}
