<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price;

/**
 * @author      Magento Core Team <core@magentocommerce.com>
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
