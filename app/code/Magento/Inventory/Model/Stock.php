<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\InventoryApi\Api\Data\StockExtensionInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class Stock extends AbstractExtensibleModel implements StockInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(StockResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getStockId(): ?int
    {
        return $this->getData(self::STOCK_ID) === null ?
            null:
            (int)$this->getData(self::STOCK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStockId(?int $stockId): void
    {
        $this->setData(self::STOCK_ID, $stockId);
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName(?string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?StockExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(StockInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(StockExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
