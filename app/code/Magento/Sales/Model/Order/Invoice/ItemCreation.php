<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceItemCreationInterface;

/**
 * Class LineItem
 * @since 2.1.2
 */
class ItemCreation implements InvoiceItemCreationInterface
{
    /**
     * @var int
     * @since 2.1.2
     */
    private $orderItemId;

    /**
     * @var float
     * @since 2.1.2
     */
    private $qty;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceItemCreationExtensionInterface
     * @since 2.1.2
     */
    private $extensionAttributes;

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function setOrderItemId($orderItemId)
    {
        $this->orderItemId = $orderItemId;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.2
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemCreationExtensionInterface|null
     * @since 2.1.2
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemCreationExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.1.2
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\InvoiceItemCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
