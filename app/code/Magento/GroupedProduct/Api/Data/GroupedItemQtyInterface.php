<?php

namespace Magento\GroupedProduct\Api\Data;

interface GroupedItemQtyInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const PRODUCT_ID = 'product_id';
    const QTY        = 'qty';

    /**
     * Associated product id
     *
     * @param int|string $value
     */
    public function setProductId($value);

    /**
     * Associated product id
     *
     * @return int|string
     */
    public function getProductId();

    /**
     * @param int|string $qty
     */
    public function setQty($qty);

    /**
     * @return int
     */
    public function getQty();
}