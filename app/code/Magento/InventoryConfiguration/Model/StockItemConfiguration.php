<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;

class StockItemConfiguration extends AbstractExtensibleModel implements StockItemConfigurationInterface
{
    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->getData(self::SKU);
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku): StockItemConfigurationInterface
    {
        $this->setData(self::SKU, $sku);

        return $this;
    }

    /**
     * @return int
     */
    public function getStockId(): int
    {
        return $this->getData(self::STOCK_ID);
    }

    /**
     * @param int $stockId
     * @return $this
     */
    public function setStockId(int $stockId): StockItemConfigurationInterface
    {
        $this->setData(self::STOCK_ID, $stockId);

        return $this;
    }

    /**
     * @return float
     */
    public function getQty(): float
    {
        return $this->getData(self::QTY);
    }

    /**
     * @param float $qty
     * @return $this
     */
    public function setQty(float $qty): StockItemConfigurationInterface
    {
        $this->setData(self::QTY, $qty);

        return $this;
    }


    /**
     * @return bool
     */
    public function getIsQtyDecimal(): bool
    {
        return $this->getData(self::IS_QTY_DECIMAL);
    }

    /**
     * @param bool $isQtyDecimal
     * @return $this
     */
    public function setIsQtyDecimal(bool $isQtyDecimal): StockItemConfigurationInterface
    {
        $this->setData(self::IS_QTY_DECIMAL, $isQtyDecimal);

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowDefaultNotificationMessage(): bool
    {
        return $this->getData(self::SHOW_DEFAULT_NOTIFICATION_MESSAGE);
    }

    /**
     * @param bool $showDefaultNotificationMessage
     * @return $this
     */
    public function setShowDefaultNotificationMessage(bool $showDefaultNotificationMessage): StockItemConfigurationInterface
    {
        $this->setData(self::SHOW_DEFAULT_NOTIFICATION_MESSAGE, $showDefaultNotificationMessage);

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseConfigMinQty(): bool
    {
        return $this->getData(self::USE_CONFIG_MIN_QTY);
    }

    /**
     * @param bool $useConfigMinQty
     * @return $this
     */
    public function setUseConfigMinQty(bool $useConfigMinQty): StockItemConfigurationInterface
    {
        $this->setData(self::USE_CONFIG_MIN_QTY, $useConfigMinQty);

        return $this;
    }

    /**
     * @return float
     */
    public function getMinQty(): float
    {
        return $this->getData(self::MIN_QTY);
    }

    /**
     * @param float $minQty
     * @return $this
     */
    public function setMinQty(float $minQty): StockItemConfigurationInterface
    {
        $this->setData(self::MIN_QTY, $minQty);

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseConfigMinSaleQty(): bool
    {
        return $this->getData(self::USE_CONFIG_MIN_SALE_QTY);
    }

    /**
     * @param bool $useConfigMinSaleQty
     * @return $this
     */
    public function setUseConfigMinSaleQty(bool $useConfigMinSaleQty): StockItemConfigurationInterface
    {
        $this->setData(self::USE_CONFIG_MIN_SALE_QTY, $useConfigMinSaleQty);

        return $this;
    }

    /**
     * @return float
     */
    public function getMinSaleQty(): float
    {
        return $this->getData(self::MIN_SALE_QTY);
    }

    /**
     * @param float $minSaleQty
     * @return $this
     */
    public function setMinSaleQty(float $minSaleQty): StockItemConfigurationInterface
    {
        $this->setData(self::MIN_SALE_QTY, $minSaleQty);

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseConfigMaxSaleQty(): bool
    {
        return $this->getData(self::USE_CONFIG_MAX_SALE_QTY);
    }

    /**
     * @param bool $useConfigMaxSaleQty
     * @return $this
     */
    public function setUseConfigMaxSaleQty(bool $useConfigMaxSaleQty): StockItemConfigurationInterface
    {
        $this->setData(self::USE_CONFIG_MAX_SALE_QTY, $useConfigMaxSaleQty);

        return $this;
    }

    /**
     * @return float
     */
    public function getMaxSaleQty(): float
    {
        return $this->getData(self::MAX_SALE_QTY);
    }

    /**
     * @param float $maxSaleQty
     * @return $this
     */
    public function setMaxSaleQty(float $maxSaleQty): StockItemConfigurationInterface
    {
        $this->setData(self::MAX_SALE_QTY, $maxSaleQty);

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseConfigBackorders(): bool
    {
        return $this->getData(self::USE_CONFIG_BACKORDERS);
    }

    /**
     * @param bool $useConfigBackorders
     * @return $this
     */
    public function setUseConfigBackorders(bool $useConfigBackorders): StockItemConfigurationInterface
    {
        $this->setData(self::USE_CONFIG_BACKORDERS, $useConfigBackorders);

        return $this;
    }

    /**
     * @return int
     */
    public function getBackorders(): int
    {
        return $this->getData(self::BACKORDERS);
    }

    /**
     * @param int $backOrders
     * @return $this
     */
    public function setBackorders(int $backOrders): StockItemConfigurationInterface
    {
        $this->setData(self::BACKORDERS, $backOrders);

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseConfigNotifyStockQty(): bool
    {
        return $this->getData(self::USE_CONFIG_NOTIFY_STOCK_QTY);
    }

    /**
     * @param bool $useConfigNotifyStockQty
     * @return $this
     */
    public function setUseConfigNotifyStockQty(bool $useConfigNotifyStockQty): StockItemConfigurationInterface
    {
        $this->setData(self::USE_CONFIG_NOTIFY_STOCK_QTY, $useConfigNotifyStockQty);

        return $this;
    }

    /**
     * @return float
     */
    public function getNotifyStockQty(): float
    {
        return $this->getData(self::NOTIFY_STOCK_QTY);
    }

    /**
     * @param float $notifyStockQty
     * @return $this
     */
    public function setNotifyStockQty(float $notifyStockQty): StockItemConfigurationInterface
    {
        $this->setData(self::NOTIFY_STOCK_QTY, $notifyStockQty);

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseConfigQtyIncrements(): bool
    {
        return $this->getData(self::USE_CONFIG_QTY_INCREMENTS);
    }

    /**
     * @param bool $useConfigQtyIncrements
     * @return $this
     */
    public function setUseConfigQtyIncrements(bool $useConfigQtyIncrements): StockItemConfigurationInterface
    {
        $this->setData(self::USE_CONFIG_QTY_INCREMENTS, $useConfigQtyIncrements);

        return $this;
    }

    /**
     * @return float
     */
    public function getQtyIncrements(): float
    {
        return $this->getData(self::QTY_INCREMENTS);
    }

    /**
     * @param float $qtyIncrements
     * @return $this
     */
    public function setQtyIncrements(float $qtyIncrements): StockItemConfigurationInterface
    {
        $this->setData(self::QTY_INCREMENTS, $qtyIncrements);

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseConfigEnableQtyInc(): bool
    {
        return $this->getData(self::USE_CONFIG_ENABLE_QTY_INC);
    }

    /**
     * @param bool $useConfigEnableQtyInc
     * @return $this
     */
    public function setUseConfigEnableQtyInc(bool $useConfigEnableQtyInc): StockItemConfigurationInterface
    {
        $this->setData(self::USE_CONFIG_ENABLE_QTY_INC, $useConfigEnableQtyInc);

        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableQtyIncrements(): bool
    {
        return $this->getData(self::ENABLE_QTY_INCREMENTS);
    }

    /**
     * @param bool $enableQtyIncrements
     * @return $this
     */
    public function setEnableQtyIncrements(bool $enableQtyIncrements): StockItemConfigurationInterface
    {
        $this->setData(self::ENABLE_QTY_INCREMENTS, $enableQtyIncrements);

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseConfigManageStock(): bool
    {
        return $this->getData(self::USE_CONFIG_MANAGE_STOCK);
    }

    /**
     * @param bool $useConfigManageStock
     * @return $this
     */
    public function setUseConfigManageStock(bool $useConfigManageStock): StockItemConfigurationInterface
    {
        $this->setData(self::USE_CONFIG_MANAGE_STOCK, $useConfigManageStock);

        return $this;
    }

    /**
     * @return bool
     */
    public function getManageStock(): bool
    {
        return $this->getData(self::MANAGE_STOCK);
    }

    /**
     * @param bool $manageStock
     * @return $this
     */
    public function setManageStock(bool $manageStock): StockItemConfigurationInterface
    {
        $this->setData(self::MANAGE_STOCK, $manageStock);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsInStock(): bool
    {
        return $this->getData(self::IS_IN_STOCK);
    }

    /**
     * @param bool $isInStock
     * @return $this
     */
    public function setIsInStock(bool $isInStock): StockItemConfigurationInterface
    {
        $this->setData(self::IS_IN_STOCK, $isInStock);

        return $this;
    }

    /**
     * @return string
     */
    public function getLowStockDate(): string
    {
        return $this->getData(self::LOW_STOCK_DATE);
    }

    /**
     * @param string $lowStockDate
     * @return $this
     */
    public function setLowStockDate(string $lowStockDate): StockItemConfigurationInterface
    {
        $this->setData(self::LOW_STOCK_DATE, $lowStockDate);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDecimalDivided(): bool
    {
        return $this->getData(self::IS_DECIMAL_DIVIDED);
    }

    /**
     * @param bool $isDecimalDivided
     * @return $this
     */
    public function setIsDecimalDivided(bool $isDecimalDivided): StockItemConfigurationInterface
    {
        $this->setData(self::IS_DECIMAL_DIVIDED, $isDecimalDivided);

        return $this;
    }

    /**
     * @return int
     */
    public function getStockStatusChangedAuto(): int
    {
        return $this->getData(self::STOCK_STATUS_CHANGED_AUTO);
    }

    /**
     * @param int $stockStatusChangedAuto
     * @return $this
     */
    public function setStockStatusChangedAuto(int $stockStatusChangedAuto): StockItemConfigurationInterface
    {
        $this->setData(self::STOCK_STATUS_CHANGED_AUTO, $stockStatusChangedAuto);

        return $this;
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface|null
     */
    public function getExtensionAttributes(): \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface $extensionAttributes
    ): StockItemConfigurationInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}