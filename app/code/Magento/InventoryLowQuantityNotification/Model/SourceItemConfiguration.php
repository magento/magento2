<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model;

use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationExtensionInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * @inheritdoc
 */
class SourceItemConfiguration extends AbstractExtensibleModel implements SourceItemConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getSourceCode(): ?string
    {
        return $this->getData(self::SOURCE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setSourceCode(string $sourceCode): void
    {
        $this->setData(self::SOURCE_CODE, $sourceCode);
    }

    /**
     * @inheritdoc
     */
    public function getNotifyStockQty(): ?float
    {
        return $this->getData(self::INVENTORY_NOTIFY_QTY) === null ?
            null:
            (float)$this->getData(self::INVENTORY_NOTIFY_QTY);
    }

    /**
     * @inheritdoc
     */
    public function setNotifyStockQty(?float $quantity): void
    {
        $this->setData(self::INVENTORY_NOTIFY_QTY, $quantity);
    }

    /**
     * @inheritdoc
     */
    public function getSku(): ?string
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setSku(string $sku): void
    {
        $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?SourceItemConfigurationExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceItemConfigurationInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceItemConfigurationExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
