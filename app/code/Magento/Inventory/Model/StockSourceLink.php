<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\InventoryApi\Api\Data\StockSourceLinkExtensionInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class StockSourceLink extends AbstractExtensibleModel implements StockSourceLinkInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(StockSourceLinkResourceModel::class);
    }

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
    public function setSourceCode(?string $sourceCode): void
    {
        $this->setData(self::SOURCE_CODE, $sourceCode);
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
    public function getPriority(): ?int
    {
        return $this->getData(self::PRIORITY) === null ?
            null:
            (int)$this->getData(self::PRIORITY);
    }

    /**
     * @inheritdoc
     */
    public function setPriority(?int $priority): void
    {
        $this->setData(self::PRIORITY, $priority);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?StockSourceLinkExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(StockSourceLinkInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(StockSourceLinkExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
