<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferExtensionInterface;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferInterface;

class PartialInventoryTransfer extends AbstractSimpleObject implements PartialInventoryTransferInterface
{

    /**
     * @return \Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferItemInterface[]
     */
    public function getItems(): array
    {
        return $this->_get(self::ITEMS);
    }

    /**
     * @param \Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferItemInterface[] $items
     */
    public function setItems(array $items): void
    {
        $this->setData(self::ITEMS, $items);
    }

    /**
     * @return string
     */
    public function getOriginSourceCode(): string
    {
        return $this->_get(self::ORIGIN_SOURCE_CODE);
    }

    /**
     * @param string $code
     */
    public function setOriginSourceCode(string $code): void
    {
        $this->setData(self::ORIGIN_SOURCE_CODE, $code);
    }

    /**
     * @return string
     */
    public function getDestinationSourceCode(): string
    {
        return $this->_get(self::DESTINATION_SOURCE_CODE);
    }

    /**
     * @param string $code
     */
    public function setDestinationSourceCode(string $code): void
    {
        $this->setData(self::DESTINATION_SOURCE_CODE, $code);
    }

    /**
     * @return \Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferExtensionInterface
    {
        return $this->_get(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @param PartialInventoryTransferExtensionInterface $extensionAttributes
     */
    public function setExtensionAttributes(\Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferExtensionInterface $extensionAttributes): void
    {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}