<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferItemInterface;

class PartialInventoryTransferItem extends AbstractSimpleObject implements PartialInventoryTransferItemInterface
{

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->_get(self::SKU);
    }

    /**
     * @param string $sku
     */
    public function setSku(string $sku): void
    {
        $this->setData(self::SKU, $sku);
    }

    /**
     * @return float
     */
    public function getQty(): float
    {
        return $this->_get(self::QTY);
    }

    /**
     * @param float $qty
     */
    public function setQty(float $qty): void
    {
        $this->setData(self::QTY, $qty);
    }
}