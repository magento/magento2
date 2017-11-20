<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\SourceItem;

/**
 * Represents relation between stock and sku list
 */
class SkuListInStockToUpdate
{
    /**
     * @var int
     */
    private $stockId;

    /**
     * @var array
     */
    private $skuList;

    /**
     * @return int
     */
    public function getStockId(): int
    {
        return $this->stockId;
    }

    /**
     * @param int $stockId
     * @return void
     */
    public function setStockId(int $stockId)
    {
        $this->stockId = $stockId;
    }

    /**
     * @return array
     */
    public function getSkuList(): array
    {
        return $this->skuList;
    }

    /**
     * @param array $skuList
     * @return void
     */
    public function setSkuList(array $skuList)
    {
        $this->skuList = $skuList;
    }
}
