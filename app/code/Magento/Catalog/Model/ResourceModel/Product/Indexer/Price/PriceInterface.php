<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Catalog Product Type Price Indexer interface
 *
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
interface PriceInterface
{
    /**
     * Reindex temporary (price result data) for all products
     *
     * @return $this
     * @since 2.0.0
     */
    public function reindexAll();

    /**
     * Reindex temporary (price result data) for defined product(s)
     *
     * @param int|array $entityIds
     * @return $this
     * @since 2.0.0
     */
    public function reindexEntity($entityIds);
}
