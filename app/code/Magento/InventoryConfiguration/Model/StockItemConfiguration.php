<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface;

/**
 * @inheritdoc
 */
class StockItemConfiguration implements StockItemConfigurationInterface
{
    /**
     * @var StockItemInterface
     */
    private $stockItem;

    /**
     * @param string $stockItem
     */
    public function __construct(
        string $stockItem
    ) {
        $this->stockItem = $stockItem;
    }

    /**
     * @inheritdoc
     */
    public function isQtyDecimal(): bool
    {
        return $this->stockItem->getIsQtyDecimal();
    }

    /**
     * @inheritdoc
     */
    public function isShowDefaultNotificationMessage(): bool
    {
        return $this->stockItem->getShowDefaultNotificationMessage();
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigMinQty(): bool
    {
        return $this->stockItem->getUseConfigMinQty();
    }

    /**
     * @inheritdoc
     */
    public function getMinQty(): float
    {
        return $this->stockItem->getMinQty();
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigMinSaleQty(): bool
    {
        return (bool)$this->stockItem->getUseConfigMinSaleQty();
    }

    /**
     * @inheritdoc
     */
    public function getMinSaleQty(): float
    {
        return $this->stockItem->getMinSaleQty();
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigMaxSaleQty(): bool
    {
        return $this->stockItem->getUseConfigMaxSaleQty();
    }

    /**
     * @inheritdoc
     */
    public function getMaxSaleQty(): float
    {
        return $this->stockItem->getMaxSaleQty();
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigBackorders(): bool
    {
        return $this->stockItem->getUseConfigBackorders();
    }

    /**
     * @inheritdoc
     */
    public function getBackorders(): int
    {
        return $this->stockItem->getBackorders();
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigNotifyStockQty(): bool
    {
        return $this->stockItem->getUseConfigNotifyStockQty();
    }

    /**
     * @inheritdoc
     */
    public function getNotifyStockQty(): float
    {
        return $this->stockItem->getNotifyStockQty();
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigQtyIncrements(): bool
    {
        return $this->stockItem->getUseConfigQtyIncrements();
    }

    /**
     * @return float
     */
    public function getQtyIncrements(): float
    {
        return $this->stockItem->getQtyIncrements();
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigEnableQtyInc(): bool
    {
        return $this->stockItem->getUseConfigEnableQtyInc();
    }

    /**
     * @inheritdoc
     */
    public function isEnableQtyIncrements(): bool
    {
        return $this->stockItem->getEnableQtyIncrements();
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigManageStock(): bool
    {
        return $this->stockItem->getUseConfigManageStock();
    }

    /**
     * @inheritdoc
     */
    public function isManageStock(): bool
    {
        return $this->stockItem->getManageStock();
    }

    /**
     * @inheritdoc
     */
    public function getLowStockDate(): string
    {
        return $this->stockItem->getLowStockDate();
    }

    /**
     * @inheritdoc
     */
    public function isDecimalDivided(): bool
    {
        return $this->stockItem->getIsDecimalDivided();
    }

    /**
     * @inheritdoc
     */
    public function getStockStatusChangedAuto(): int
    {
        return $this->stockItem->getStockStatusChangedAuto();
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): StockItemConfigurationExtensionInterface
    {
        // TODO: not implemented
    }
}
