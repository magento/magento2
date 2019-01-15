<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;

/**
 * Class LineItem
 */
class ItemCreation implements CreditmemoItemCreationInterface
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
     * @var \Magento\Sales\Api\Data\CreditmemoItemCreationExtensionInterface
     */
    private $extensionAttributes;

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
        return $this;
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
        return $this;
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemCreationExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoItemCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
