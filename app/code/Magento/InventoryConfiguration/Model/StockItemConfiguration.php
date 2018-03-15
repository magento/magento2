<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;

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
     * @param StockItemInterface $stockItem
     */
    public function __construct(
        StockItemInterface $stockItem
    ) {
        $this->stockItem = $stockItem;
    }

    /**
     * @inheritdoc
     */
    public function isQtyDecimal(): bool
    {
        return (bool)$this->stockItem->getIsQtyDecimal();
    }

    /**
     * @inheritdoc
     */
    public function setIsQtyDecimal(bool $isQtyDecimal): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setIsQtyDecimal($isQtyDecimal);
        return $this;
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
        return (bool)$this->stockItem->getUseConfigMinQty();
    }

    /**
     * @inheritdoc
     */
    public function setUseConfigMinQty(bool $useConfigMinQty): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setUseConfigMinQty($useConfigMinQty);
        return $this;
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
    public function setMinQty(float $minQty): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setMinQty($minQty);
        return $this;
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
    public function setUseConfigMinSaleQty(bool $useConfigMinSaleQty): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setUseConfigMinSaleQty($useConfigMinSaleQty);
        return $this;
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
    public function setMinSaleQty(float $minSaleQty): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setMinSaleQty($minSaleQty);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigMaxSaleQty(): bool
    {
        return (bool)$this->stockItem->getUseConfigMaxSaleQty();
    }

    /**
     * @inheritdoc
     */
    public function setUseConfigMaxSaleQty(bool $useConfigMaxSaleQty): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setUseConfigMaxSaleQty($useConfigMaxSaleQty);
        return $this;
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
    public function setMaxSaleQty(float $maxSaleQty): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setMaxSaleQty($maxSaleQty);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigBackorders(): bool
    {
        return (bool)$this->stockItem->getUseConfigBackorders();
    }

    /**
     * @inheritdoc
     */
    public function setUseConfigBackorders(bool $useConfigBackorders): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setUseConfigBackorders($useConfigBackorders);
        return $this;
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
    public function setBackorders(int $backOrders): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setBackorders($backOrders);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigNotifyStockQty(): bool
    {
        return (bool)$this->stockItem->getUseConfigNotifyStockQty();
    }

    /**
     * @inheritdoc
     */
    public function setUseConfigNotifyStockQty(bool $useConfigNotifyStockQty): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setUseConfigNotifyStockQty($useConfigNotifyStockQty);
        return $this;
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
    public function setNotifyStockQty(float $notifyStockQty): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setNotifyStockQty($notifyStockQty);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigQtyIncrements(): bool
    {
        return (bool)$this->stockItem->getUseConfigQtyIncrements();
    }

    /**
     * @inheritdoc
     */
    public function setUseConfigQtyIncrements(bool $useConfigQtyIncrements): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setUseConfigQtyIncrements($useConfigQtyIncrements);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQtyIncrements(): float
    {
        $qtyIncrements = $this->stockItem->getQtyIncrements();
        if (false === $qtyIncrements) {
            return 1;
        }
        return $qtyIncrements;
    }

    /**
     * @inheritdoc
     */
    public function setQtyIncrements(float $qtyIncrements): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setQtyIncrements($qtyIncrements);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigEnableQtyInc(): bool
    {
        return (bool)$this->stockItem->getUseConfigEnableQtyInc();
    }

    /**
     * @inheritdoc
     */
    public function setUseConfigEnableQtyInc(bool $useConfigEnableQtyInc): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setUseConfigEnableQtyInc($useConfigEnableQtyInc);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEnableQtyIncrements(): bool
    {
        return (bool)$this->stockItem->getEnableQtyIncrements();
    }

    /**
     * @inheritdoc
     */
    public function setEnableQtyIncrements(bool $enableQtyIncrements): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setEnableQtyIncrements($enableQtyIncrements);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isUseConfigManageStock(): bool
    {
        return (bool)$this->stockItem->getUseConfigManageStock();
    }

    /**
     * @inheritdoc
     */
    public function setUseConfigManageStock(bool $useConfigManageStock): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setUseConfigManageStock($useConfigManageStock);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isManageStock(): bool
    {
        return (bool)$this->stockItem->getManageStock();
    }

    /**
     * @inheritdoc
     */
    public function setManageStock(bool $manageStock): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setManageStock($manageStock);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLowStockDate(): string
    {
        $lowStockDate = $this->stockItem->getLowStockDate();
        return null === $lowStockDate ? '': $lowStockDate;
    }

    /**
     * @inheritdoc
     */
    public function setLowStockDate(string $lowStockDate): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setLowStockDate($lowStockDate);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isDecimalDivided(): bool
    {
        return (bool)$this->stockItem->getIsDecimalDivided();
    }

    /**
     * @inheritdoc
     */
    public function setIsDecimalDivided(bool $isDecimalDivided): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setIsDecimalDivided($isDecimalDivided);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStockStatusChangedAuto(): bool
    {
        return (bool) $this->stockItem->getStockStatusChangedAuto();
    }

    /**
     * @inheritdoc
     */
    public function setStockStatusChangedAuto(int $stockStatusChangedAuto): StockItemConfigurationInterface
    {
        $this->stockItem = $this->stockItem->setStockStatusChangedAuto($stockStatusChangedAuto);
        return $this;
    }
}
