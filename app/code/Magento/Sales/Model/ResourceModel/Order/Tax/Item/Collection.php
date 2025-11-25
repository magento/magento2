<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Tax\Item;

class Collection extends \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Tax\Item::class,
            \Magento\Sales\Model\ResourceModel\Order\Tax\Item::class
        );
    }
}
