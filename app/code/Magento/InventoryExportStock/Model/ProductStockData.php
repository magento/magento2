<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\InventoryExportStockApi\Api\Data\ProductStockDataInterface;

/**
 * Class ProductStockData
 */
class ProductStockData extends AbstractModel implements ProductStockDataInterface
{
    /**
     * @inheritDoc
     */
    public function setSku(string $sku): void
    {
        $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritDoc
     */
    public function getSku(): string
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritDoc
     */
    public function setQty(int $qty): void
    {
        $this->setData(self::QTY, $qty);
    }

    /**
     * @inheritDoc
     */
    public function getQty(): int
    {
        return $this->getData(self::QTY);
    }
}
