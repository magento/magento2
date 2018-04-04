<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Model;

/**
 * Provide corresponding product ids for given source item ids.
 */
class GetProductIdsBySourceItemIds
{
    /**
     * @var ResourceModel\GetProductIdsBySourceItemIds
     */
    private $resource;

    /**
     * GetProductIdsBySourceItemIds constructor.
     * @param ResourceModel\GetProductIdsBySourceItemIds $resource
     */
    public function __construct(\Magento\InventoryCatalog\Model\ResourceModel\GetProductIdsBySourceItemIds $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get product ids by source items ids.
     *
     * @param array $sourceItemIds
     * @return array
     * @throws \Exception in case catalog product entity type hasn't been initialize.
     */
    public function execute(array $sourceItemIds): array
    {
        return $this->resource->execute($sourceItemIds);
    }
}
