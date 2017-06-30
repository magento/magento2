<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use \Magento\InventoryApi\Api\Data\SourceItemInterface;

class SourceItem extends AbstractExtensibleModel implements SourceItemInterface
{
    /**
     * @inheritdoc
     */
    public function getSku()
    {
        return $this->getData(SourceItemInterface::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        $this->setData(SourceItemInterface::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function getSourceId()
    {
        return $this->getData(SourceItemInterface::SOURCE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getSourceItemId()
    {
        return $this->getData(SourceItemInterface::SOURCE_ITEM_ID);
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->getData(SourceItemInterface::QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity)
    {
        $this->setData(SourceItemInterface::QUANTITY, $quantity);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(SourceItemInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(SourceItemInterface::STATUS, $status);
    }
}
