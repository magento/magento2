<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\DataMapper;

use Magento\Elasticsearch\Model\ResourceModel\StockInventory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Stock
{
    /**
     * @var StockInventory
     */
    private $inventory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StockInventory $inventory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StockInventory        $inventory,
        StoreManagerInterface $storeManager
    ) {
        $this->inventory = $inventory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $entityId
     * @param $storeId
     * @return array|int[]
     * @throws NoSuchEntityException
     */
    public function map($entityId, $storeId): array
    {
        $sku = $this->inventory->getSkuRelation((int)$entityId);

        if (!$sku) {
            return ['is_salable' => 1];
        }

        $value = $this->inventory->getStockStatus(
            $sku,
            $this->storeManager->getStore($storeId)->getWebsite()->getCode()
        );

        return ['is_salable' => $value];
    }
}
