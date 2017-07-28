<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price;

/**
 * @api
 * @since 2.2.0
 */
interface GroupedInterface
{
    /**
     * Reindex for all products
     *
     * @return $this
     * @since 2.2.0
     */
    public function reindexAll();

    /**
     * Reindex for defined product(s)
     *
     * @param int|array $entityIds
     * @return $this
     * @since 2.2.0
     */
    public function reindexEntity($entityIds);
}
