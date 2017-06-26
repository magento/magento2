<?php

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use \Magento\InventoryApi\Api\Data\SourceItemInterface;

class SourceItem extends AbstractExtensibleModel implements SourceItemInterface
{
    /**
     * @return int
     */
    public function getSku()
    {
        return $this->getData(SourceItemInterface::SKU);
    }

    /**
     * @param $sku
     * @return int
     */
    public function setSku($sku)
    {
        $this->setData(SourceItemInterface::SKU, $sku);
    }

    /**
     * @return int
     */
    public function getSourceId()
    {
        return $this->getData(SourceItemInterface::SOURCE_ID);
    }

    /**
     * @return int
     */
    public function getSourceItemId()
    {
        return $this->getData(SourceItemInterface::SOURCE_ITEM_ID);
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->getData(SourceItemInterface::QUANTITY);
    }

    /**
     * @param $quantity
     * @return float
     */
    public function setQuantity($quantity)
    {
        $this->setData(SourceItemInterface::QUANTITY, $quantity);
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(SourceItemInterface::STATUS);
    }

    public function setStatus($status)
    {
        $this->setData(SourceItemInterface::STATUS, $status);
    }
}