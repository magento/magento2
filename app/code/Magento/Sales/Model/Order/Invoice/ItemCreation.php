<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceItemCreationInterface;

/**
 * Class LineItem
 */
class ItemCreation implements InvoiceItemCreationInterface
{
    /**
     * @var int
     */
    private $orderItemId;

    /**
     * @var float
     */
    private $qty;

    /**
     * {@inheritdoc}
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderItemId($orderItemId)
    {
        $this->orderItemId = $orderItemId;
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }
}
