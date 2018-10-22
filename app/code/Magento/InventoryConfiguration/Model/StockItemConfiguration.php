<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @inheritdoc
 */
class StockItemConfiguration extends AbstractExtensibleModel implements StockItemConfigurationInterface
{
    /**
     * @var StockItemInterface
     */
    private $stockItem;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param StockItemInterface $stockItem
     * @param ScopeConfigInterface $scopeConfig
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StockItemInterface $stockItem,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->stockItem = $stockItem;
        $this->scopeConfig = $scopeConfig;
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
    public function setIsQtyDecimal(bool $isQtyDecimal): void
    {
        $this->stockItem->setIsQtyDecimal($isQtyDecimal);
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
    public function setUseConfigMinQty(bool $useConfigMinQty): void
    {
        $this->stockItem = $this->stockItem->setUseConfigMinQty($useConfigMinQty);
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
    public function setMinQty(float $minQty): void
    {
        $this->stockItem->setMinQty($minQty);
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
    public function setUseConfigMinSaleQty(bool $useConfigMinSaleQty): void
    {
        $this->stockItem->setUseConfigMinSaleQty($useConfigMinSaleQty);
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
    public function setMinSaleQty(float $minSaleQty): void
    {
        $this->stockItem->setMinSaleQty($minSaleQty);
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
    public function setUseConfigMaxSaleQty(bool $useConfigMaxSaleQty): void
    {
        $this->stockItem->setUseConfigMaxSaleQty($useConfigMaxSaleQty);
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
    public function setMaxSaleQty(float $maxSaleQty): void
    {
        $this->stockItem->setMaxSaleQty($maxSaleQty);
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
    public function setUseConfigBackorders(bool $useConfigBackorders): void
    {
        $this->stockItem->setUseConfigBackorders($useConfigBackorders);
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
    public function setBackorders(int $backOrders): void
    {
        $this->stockItem->setBackorders($backOrders);
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
    public function setUseConfigNotifyStockQty(bool $useConfigNotifyStockQty): void
    {
        $this->stockItem->setUseConfigNotifyStockQty($useConfigNotifyStockQty);
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
    public function setNotifyStockQty(float $notifyStockQty): void
    {
        $this->stockItem->setNotifyStockQty($notifyStockQty);
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
    public function setUseConfigQtyIncrements(bool $useConfigQtyIncrements): void
    {
        $this->stockItem->setUseConfigQtyIncrements($useConfigQtyIncrements);
    }

    /**
     * @inheritdoc
     */
    public function getQtyIncrements(): float
    {
        $qtyIncrements = $this->stockItem->getQtyIncrements();
        if (false === $qtyIncrements) {
            return 0;
        }
        return $qtyIncrements;
    }

    /**
     * @inheritdoc
     */
    public function setQtyIncrements(float $qtyIncrements): void
    {
        $this->stockItem->setQtyIncrements($qtyIncrements);
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
    public function setUseConfigEnableQtyInc(bool $useConfigEnableQtyInc): void
    {
        $this->stockItem->setUseConfigEnableQtyInc($useConfigEnableQtyInc);
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
    public function setEnableQtyIncrements(bool $enableQtyIncrements): void
    {
        $this->stockItem->setEnableQtyIncrements($enableQtyIncrements);
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
    public function setUseConfigManageStock(bool $useConfigManageStock): void
    {
        $this->stockItem->setUseConfigManageStock($useConfigManageStock);
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
    public function setManageStock(bool $manageStock): void
    {
        $this->stockItem->setManageStock($manageStock);
    }

    /**
     * @inheritdoc
     */
    public function getLowStockDate(): string
    {
        $lowStockDate = $this->stockItem->getLowStockDate();
        return null === $lowStockDate ? '' : $lowStockDate;
    }

    /**
     * @inheritdoc
     */
    public function setLowStockDate(string $lowStockDate): void
    {
        $this->stockItem->setLowStockDate($lowStockDate);
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
    public function setIsDecimalDivided(bool $isDecimalDivided): void
    {
        $this->stockItem->setIsDecimalDivided($isDecimalDivided);
    }

    /**
     * @inheritdoc
     */
    public function getStockStatusChangedAuto(): bool
    {
        return (bool)$this->stockItem->getStockStatusChangedAuto();
    }

    /**
     * @inheritdoc
     */
    public function setStockStatusChangedAuto(int $stockStatusChangedAuto): void
    {
        $this->stockItem->setStockStatusChangedAuto($stockStatusChangedAuto);
    }

    /**
     * @inheritdoc
     */
    public function getStockThresholdQty(): float
    {
        return (float)$this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_STOCK_THRESHOLD_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?StockItemConfigurationExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(StockItemConfigurationInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(StockItemConfigurationExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
