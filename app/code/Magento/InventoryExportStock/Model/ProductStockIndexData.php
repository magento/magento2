<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\InventoryExportStockApi\Api\Data\ProductStockIndexDataInterface;

/**
 * Class ProductStockIndexData
 */
class ProductStockIndexData extends AbstractModel implements ProductStockIndexDataInterface
{
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
    public function getQty(): float
    {
        return $this->getData(self::QTY);
    }

    /**
     * @inheritDoc
     */
    public function getIsSalable(): bool
    {
        return $this->getData(self::IS_SALABLE);
    }

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
    public function setQty(float $qty): void
    {
        $this->setData(self::QTY, $qty);
    }

    /**
     * @inheritDoc
     */
    public function setIsSalable(bool $isSalable): void
    {
        $this->setData(self::IS_SALABLE, $isSalable);
    }
}
