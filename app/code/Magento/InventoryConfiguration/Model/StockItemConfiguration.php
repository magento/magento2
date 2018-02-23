<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface;

/**
 * Class replaces StockItemConfigurationInterface object with StockItemInterface object
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class StockItemConfiguration implements StockItemConfigurationInterface
{
    /**
     * @var StockItemInterface
     */
    private $legacyStockItem;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var int
     */
    private $stockId;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param StockItemRepository $stockItemRepository
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param string $sku
     * @param int $stockId
     */
    public function __construct(
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockItemRepository $stockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        string $sku,
        int $stockId
    ) {
        $this->sku = $sku;
        $this->stockId = $stockId;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @return StockItemInterface
     */
    private function getLegacyStockItem()
    {
        if (!$this->legacyStockItem) {
            $productId = $this->getProductIdsBySkus->execute([$this->sku])[$this->sku];
            $searchCriteria = $this->stockItemCriteriaFactory->create();
            $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productId);
            $searchCriteria->addFilter(StockItemInterface::STOCK_ID, StockItemInterface::STOCK_ID, $this->stockId);
            $this->legacyStockItem = $this->stockItemRepository->getList($searchCriteria)[0];
        }
        return $this->legacyStockItem;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return int
     */
    public function getStockId(): int
    {
        return $this->getLegacyStockItem()->getStockId();
    }

    /**
     * @return bool
     */
    public function getIsQtyDecimal(): bool
    {
        return $this->getLegacyStockItem()->getIsQtyDecimal();
    }

    /**
     * @param bool $isQtyDecimal
     * @return void
     */
    public function setIsQtyDecimal(bool $isQtyDecimal): void
    {
        $this->getLegacyStockItem()->setIsQtyDecimal($isQtyDecimal);
    }

    /**
     * @return bool
     */
    public function getShowDefaultNotificationMessage(): bool
    {
        return $this->getLegacyStockItem()->getShowDefaultNotificationMessage();
    }

    /**
     * @param bool $showDefaultNotificationMessage
     * @return void
     */
    public function setShowDefaultNotificationMessage(bool $showDefaultNotificationMessage): void
    {
        $this->getLegacyStockItem()->setShowDefaultNotificationMessage($showDefaultNotificationMessage);
    }

    /**
     * @return bool
     */
    public function getUseConfigMinQty(): bool
    {
        return $this->getLegacyStockItem()->getUseConfigMinQty();
    }

    /**
     * @param bool $useConfigMinQty
     * @return void
     */
    public function setUseConfigMinQty(bool $useConfigMinQty): void
    {
        $this->getLegacyStockItem()->setUseConfigMinQty($useConfigMinQty);
    }

    /**
     * @return float
     */
    public function getMinQty(): float
    {
        return $this->getLegacyStockItem()->getMinQty();
    }

    /**
     * @param float $minQty
     * @return void
     */
    public function setMinQty(float $minQty): void
    {
        $this->getLegacyStockItem()->setMinQty($minQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigMinSaleQty(): bool
    {
        return (bool)$this->getLegacyStockItem()->getUseConfigMinSaleQty();
    }

    /**
     * @param bool $useConfigMinSaleQty
     * @return void
     */
    public function setUseConfigMinSaleQty(bool $useConfigMinSaleQty): void
    {
        $this->getLegacyStockItem()->setUseConfigMinSaleQty($useConfigMinSaleQty);
    }

    /**
     * @return float
     */
    public function getMinSaleQty(): float
    {
        return $this->getLegacyStockItem()->getMinSaleQty();
    }

    /**
     * @param float $minSaleQty
     * @return void
     */
    public function setMinSaleQty(float $minSaleQty): void
    {
        $this->getLegacyStockItem()->setMinSaleQty($minSaleQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigMaxSaleQty(): bool
    {
        return $this->getLegacyStockItem()->getUseConfigMaxSaleQty();
    }

    /**
     * @param bool $useConfigMaxSaleQty
     * @return void
     */
    public function setUseConfigMaxSaleQty(bool $useConfigMaxSaleQty): void
    {
        $this->getLegacyStockItem()->setUseConfigMaxSaleQty($useConfigMaxSaleQty);
    }

    /**
     * @return float
     */
    public function getMaxSaleQty(): float
    {
        return $this->getLegacyStockItem()->getMaxSaleQty();
    }

    /**
     * @param float $maxSaleQty
     * @return void
     */
    public function setMaxSaleQty(float $maxSaleQty): void
    {
        $this->getLegacyStockItem()->setMaxSaleQty($maxSaleQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigBackorders(): bool
    {
        return $this->getLegacyStockItem()->getUseConfigBackorders();
    }

    /**
     * @param bool $useConfigBackorders
     * @return void
     */
    public function setUseConfigBackorders(bool $useConfigBackorders): void
    {
        $this->getLegacyStockItem()->setUseConfigBackorders($useConfigBackorders);
    }

    /**
     * @return int
     */
    public function getBackorders(): int
    {
        return $this->getLegacyStockItem()->getBackorders();
    }

    /**
     * @param int $backOrders
     * @return void
     */
    public function setBackorders(int $backOrders): void
    {
        $this->getLegacyStockItem()->setBackorders($backOrders);
    }

    /**
     * @return bool
     */
    public function getUseConfigNotifyStockQty(): bool
    {
        return $this->getLegacyStockItem()->getUseConfigNotifyStockQty();
    }

    /**
     * @param bool $useConfigNotifyStockQty
     * @return void
     */
    public function setUseConfigNotifyStockQty(bool $useConfigNotifyStockQty): void
    {
        $this->getLegacyStockItem()->setUseConfigNotifyStockQty($useConfigNotifyStockQty);
    }

    /**
     * @return float
     */
    public function getNotifyStockQty(): float
    {
        return $this->getLegacyStockItem()->getNotifyStockQty();
    }

    /**
     * @param float $notifyStockQty
     * @return void
     */
    public function setNotifyStockQty(float $notifyStockQty): void
    {
        $this->getLegacyStockItem()->setNotifyStockQty($notifyStockQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigQtyIncrements(): bool
    {
        return $this->getLegacyStockItem()->getUseConfigQtyIncrements();
    }

    /**
     * @param bool $useConfigQtyIncrements
     * @return void
     */
    public function setUseConfigQtyIncrements(bool $useConfigQtyIncrements): void
    {
        $this->getLegacyStockItem()->setUseConfigQtyIncrements($useConfigQtyIncrements);
    }

    /**
     * @return float
     */
    public function getQtyIncrements(): float
    {
        return $this->getLegacyStockItem()->getQtyIncrements();
    }

    /**
     * @param float $qtyIncrements
     * @return void
     */
    public function setQtyIncrements(float $qtyIncrements): void
    {
        $this->getLegacyStockItem()->setQtyIncrements($qtyIncrements);
    }

    /**
     * @return bool
     */
    public function getUseConfigEnableQtyInc(): bool
    {
        return $this->getLegacyStockItem()->getUseConfigEnableQtyInc();
    }

    /**
     * @param bool $useConfigEnableQtyInc
     * @return void
     */
    public function setUseConfigEnableQtyInc(bool $useConfigEnableQtyInc): void
    {
        $this->getLegacyStockItem()->setUseConfigEnableQtyInc($useConfigEnableQtyInc);
    }

    /**
     * @return bool
     */
    public function getEnableQtyIncrements(): bool
    {
        return $this->getLegacyStockItem()->getEnableQtyIncrements();
    }

    /**
     * @param bool $enableQtyIncrements
     * @return void
     */
    public function setEnableQtyIncrements(bool $enableQtyIncrements): void
    {
        $this->getLegacyStockItem()->setEnableQtyIncrements($enableQtyIncrements);
    }

    /**
     * @return bool
     */
    public function getUseConfigManageStock(): bool
    {
        return $this->getLegacyStockItem()->getUseConfigManageStock();
    }

    /**
     * @param bool $useConfigManageStock
     * @return void
     */
    public function setUseConfigManageStock(bool $useConfigManageStock): void
    {
        $this->getLegacyStockItem()->setUseConfigManageStock($useConfigManageStock);
    }

    /**
     * @return bool
     */
    public function getManageStock(): bool
    {
        return $this->getLegacyStockItem()->getManageStock();
    }

    /**
     * @param bool $manageStock
     * @return void
     */
    public function setManageStock(bool $manageStock): void
    {
        $this->getLegacyStockItem()->setManageStock($manageStock);
    }

    /**
     * @return string
     */
    public function getLowStockDate(): string
    {
        return $this->getLegacyStockItem()->getLowStockDate();
    }

    /**
     * @param string $lowStockDate
     * @return void
     */
    public function setLowStockDate(string $lowStockDate): void
    {
        $this->getLegacyStockItem()->setLowStockDate($lowStockDate);
    }

    /**
     * @return bool
     */
    public function getIsDecimalDivided(): bool
    {
        return $this->getLegacyStockItem()->getIsDecimalDivided();
    }

    /**
     * @param bool $isDecimalDivided
     * @return void
     */
    public function setIsDecimalDivided(bool $isDecimalDivided): void
    {
        $this->getLegacyStockItem()->setIsDecimalDivided($isDecimalDivided);
    }

    /**
     * @return int
     */
    public function getStockStatusChangedAuto(): int
    {
        return $this->getLegacyStockItem()->getStockStatusChangedAuto();
    }

    /**
     * @param int $stockStatusChangedAuto
     * @return void
     */
    public function setStockStatusChangedAuto(int $stockStatusChangedAuto): void
    {
        $this->getLegacyStockItem()->setStockStatusChangedAuto($stockStatusChangedAuto);
    }

    /**
     * @return StockItemConfigurationExtensionInterface
     */
    public function getExtensionAttributes(): StockItemConfigurationExtensionInterface
    {
        return $this->getLegacyStockItem()->getExtensionAttributes();
    }

    /**
     * @param StockItemConfigurationExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(StockItemConfigurationExtensionInterface $extensionAttributes): void
    {
        $this->getLegacyStockItem()->setExtensionAttributes($extensionAttributes);
    }
}
