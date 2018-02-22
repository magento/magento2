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
 */
class StockItemConfiguration implements StockItemConfigurationInterface
{
    /**
     * @var StockItemInterface
     */
    private $subject;
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
     * StockItemConfiguration constructor.
     * @param string $sku
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param StockItemRepository $stockItemRepository
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param int $stockId
     */
    public function __construct(
        string $sku,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockItemRepository $stockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
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
    private function getSubject()
    {
        if (!$this->subject) {
            $productId = $this->getProductIdsBySkus->execute([$this->sku])[$this->sku];
            $searchCriteria = $this->stockItemCriteriaFactory->create();
            $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productId);
            $searchCriteria->addFilter(StockItemInterface::STOCK_ID, StockItemInterface::STOCK_ID, $this->stockId);
            $this->subject = $this->stockItemRepository->getList($searchCriteria)[0];
        }
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku): void
    {
        $productId = $this->getProductIdsBySkus->execute([$this->sku])[$this->sku];
        $this->getSubject()->setProductId($productId);
    }

    /**
     * @return int
     */
    public function getStockId(): int
    {
        return $this->getSubject()->getStockId();
    }

    /**
     * @param int $stockId
     * @return void
     */
    public function setStockId(int $stockId): void
    {
        $this->getSubject()->setStockId($stockId);
    }

    /**
     * @return float
     */
    public function getQty(): float
    {
        return $this->getSubject()->getQty();
    }

    /**
     * @param float $qty
     * @return void
     */
    public function setQty(float $qty): void
    {
        $this->getSubject()->setQty($qty);
    }

    /**
     * @return bool
     */
    public function getIsQtyDecimal(): bool
    {
        return $this->getSubject()->getIsQtyDecimal();
    }

    /**
     * @param bool $isQtyDecimal
     * @return void
     */
    public function setIsQtyDecimal(bool $isQtyDecimal): void
    {
        $this->getSubject()->setIsQtyDecimal($isQtyDecimal);
    }

    /**
     * @return bool
     */
    public function getShowDefaultNotificationMessage(): bool
    {
        return $this->getSubject()->getShowDefaultNotificationMessage();
    }

    /**
     * @param bool $showDefaultNotificationMessage
     * @return void
     */
    public function setShowDefaultNotificationMessage(bool $showDefaultNotificationMessage): void
    {
        $this->getSubject()->setShowDefaultNotificationMessage($showDefaultNotificationMessage);
    }

    /**
     * @return bool
     */
    public function getUseConfigMinQty(): bool
    {
        return $this->getSubject()->getUseConfigMinQty();
    }

    /**
     * @param bool $useConfigMinQty
     * @return void
     */
    public function setUseConfigMinQty(bool $useConfigMinQty): void
    {
        $this->getSubject()->setUseConfigMinQty($useConfigMinQty);
    }

    /**
     * @return float
     */
    public function getMinQty(): float
    {
        return $this->getSubject()->getMinQty();
    }

    /**
     * @param float $minQty
     * @return void
     */
    public function setMinQty(float $minQty): void
    {
        $this->getSubject()->setMinQty($minQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigMinSaleQty(): bool
    {
        return (bool)$this->getSubject()->getUseConfigMinSaleQty();
    }

    /**
     * @param bool $useConfigMinSaleQty
     * @return void
     */
    public function setUseConfigMinSaleQty(bool $useConfigMinSaleQty): void
    {
        $this->getSubject()->setUseConfigMinSaleQty($useConfigMinSaleQty);
    }

    /**
     * @return float
     */
    public function getMinSaleQty(): float
    {
        return $this->getSubject()->getMinSaleQty();
    }

    /**
     * @param float $minSaleQty
     * @return void
     */
    public function setMinSaleQty(float $minSaleQty): void
    {
        $this->getSubject()->setMinSaleQty($minSaleQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigMaxSaleQty(): bool
    {
        return $this->getSubject()->getUseConfigMaxSaleQty();
    }

    /**
     * @param bool $useConfigMaxSaleQty
     * @return void
     */
    public function setUseConfigMaxSaleQty(bool $useConfigMaxSaleQty): void
    {
        $this->getSubject()->setUseConfigMaxSaleQty($useConfigMaxSaleQty);
    }

    /**
     * @return float
     */
    public function getMaxSaleQty(): float
    {
        return $this->getSubject()->getMaxSaleQty();
    }

    /**
     * @param float $maxSaleQty
     * @return void
     */
    public function setMaxSaleQty(float $maxSaleQty): void
    {
        $this->getSubject()->setMaxSaleQty($maxSaleQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigBackorders(): bool
    {
        return $this->getSubject()->getUseConfigBackorders();
    }

    /**
     * @param bool $useConfigBackorders
     * @return void
     */
    public function setUseConfigBackorders(bool $useConfigBackorders): void
    {
        $this->getSubject()->setUseConfigBackorders($useConfigBackorders);
    }

    /**
     * @return int
     */
    public function getBackorders(): int
    {
        return $this->getSubject()->getBackorders();
    }

    /**
     * @param int $backOrders
     * @return void
     */
    public function setBackorders(int $backOrders): void
    {
        $this->getSubject()->setBackorders($backOrders);
    }

    /**
     * @return bool
     */
    public function getUseConfigNotifyStockQty(): bool
    {
        return $this->getSubject()->getUseConfigNotifyStockQty();
    }

    /**
     * @param bool $useConfigNotifyStockQty
     * @return void
     */
    public function setUseConfigNotifyStockQty(bool $useConfigNotifyStockQty): void
    {
        $this->getSubject()->setUseConfigNotifyStockQty($useConfigNotifyStockQty);
    }

    /**
     * @return float
     */
    public function getNotifyStockQty(): float
    {
        return $this->getSubject()->getNotifyStockQty();
    }

    /**
     * @param float $notifyStockQty
     * @return void
     */
    public function setNotifyStockQty(float $notifyStockQty): void
    {
        $this->getSubject()->setNotifyStockQty($notifyStockQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigQtyIncrements(): bool
    {
        return $this->getSubject()->getUseConfigQtyIncrements();
    }

    /**
     * @param bool $useConfigQtyIncrements
     * @return void
     */
    public function setUseConfigQtyIncrements(bool $useConfigQtyIncrements): void
    {
        $this->getSubject()->setUseConfigQtyIncrements($useConfigQtyIncrements);
    }

    /**
     * @return float
     */
    public function getQtyIncrements(): float
    {
        return $this->getSubject()->getQtyIncrements();
    }

    /**
     * @param float $qtyIncrements
     * @return void
     */
    public function setQtyIncrements(float $qtyIncrements): void
    {
        $this->getSubject()->setQtyIncrements($qtyIncrements);
    }

    /**
     * @return bool
     */
    public function getUseConfigEnableQtyInc(): bool
    {
        return $this->getSubject()->getUseConfigEnableQtyInc();
    }

    /**
     * @param bool $useConfigEnableQtyInc
     * @return void
     */
    public function setUseConfigEnableQtyInc(bool $useConfigEnableQtyInc): void
    {
        $this->getSubject()->setUseConfigEnableQtyInc($useConfigEnableQtyInc);
    }

    /**
     * @return bool
     */
    public function getEnableQtyIncrements(): bool
    {
        return $this->getSubject()->getEnableQtyIncrements();
    }

    /**
     * @param bool $enableQtyIncrements
     * @return void
     */
    public function setEnableQtyIncrements(bool $enableQtyIncrements): void
    {
        $this->getSubject()->setEnableQtyIncrements($enableQtyIncrements);
    }

    /**
     * @return bool
     */
    public function getUseConfigManageStock(): bool
    {
        return $this->getSubject()->getUseConfigManageStock();
    }

    /**
     * @param bool $useConfigManageStock
     * @return void
     */
    public function setUseConfigManageStock(bool $useConfigManageStock): void
    {
        $this->getSubject()->setUseConfigManageStock($useConfigManageStock);
    }

    /**
     * @return bool
     */
    public function getManageStock(): bool
    {
        return $this->getSubject()->getManageStock();
    }

    /**
     * @param bool $manageStock
     * @return void
     */
    public function setManageStock(bool $manageStock): void
    {
        $this->getSubject()->setManageStock($manageStock);
    }

    /**
     * @return bool
     */
    public function getIsInStock(): bool
    {
        return $this->getSubject()->getIsInStock();
    }

    /**
     * @param bool $isInStock
     * @return void
     */
    public function setIsInStock(bool $isInStock): void
    {
        $this->getSubject()->setIsInStock($isInStock);
    }

    /**
     * @return string
     */
    public function getLowStockDate(): string
    {
        return $this->getSubject()->getLowStockDate();
    }

    /**
     * @param string $lowStockDate
     * @return void
     */
    public function setLowStockDate(string $lowStockDate): void
    {
        $this->getSubject()->setLowStockDate($lowStockDate);
    }

    /**
     * @return bool
     */
    public function getIsDecimalDivided(): bool
    {
        return $this->getSubject()->getIsDecimalDivided();
    }

    /**
     * @param bool $isDecimalDivided
     * @return void
     */
    public function setIsDecimalDivided(bool $isDecimalDivided): void
    {
        $this->getSubject()->setIsDecimalDivided($isDecimalDivided);
    }

    /**
     * @return int
     */
    public function getStockStatusChangedAuto(): int
    {
        return $this->getSubject()->getStockStatusChangedAuto();
    }

    /**
     * @param int $stockStatusChangedAuto
     * @return void
     */
    public function setStockStatusChangedAuto(int $stockStatusChangedAuto): void
    {
        $this->getSubject()->setStockStatusChangedAuto($stockStatusChangedAuto);
    }

    /**
     * @return StockItemConfigurationExtensionInterface
     */
    public function getExtensionAttributes(): StockItemConfigurationExtensionInterface
    {
        return $this->getSubject()->getExtensionAttributes();
    }

    /**
     * @param StockItemConfigurationExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(StockItemConfigurationExtensionInterface $extensionAttributes): void
    {
        $this->getSubject()->setExtensionAttributes($extensionAttributes);
    }

}
