<?php

namespace Magento\GroupedProduct\Model\Quote\Item;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\GroupedProduct\Api\Data\GroupedItemQtyInterface;

/**
 * Class GroupedItemQty
 */
class GroupedItemQty extends AbstractExtensibleModel implements GroupedItemQtyInterface
{
    /**
     * {@inheritdoc}
     */
    public function setProductId($value)
    {
        $this->setData(self::PRODUCT_ID, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        $this->setData(self::QTY, $qty);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return (int)$this->getData(self::QTY);
    }
}