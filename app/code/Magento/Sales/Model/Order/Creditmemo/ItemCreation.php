<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;

/**
 * Class LineItem
 * @since 2.1.3
 */
class ItemCreation implements CreditmemoItemCreationInterface
{
    /**
     * @var int
     * @since 2.1.3
     */
    private $orderItemId;

    /**
     * @var float
     * @since 2.1.3
     */
    private $qty;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoItemCreationExtensionInterface
     * @since 2.1.3
     */
    private $extensionAttributes;

    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function setOrderItemId($orderItemId)
    {
        $this->orderItemId = $orderItemId;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.3
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
     * @since 2.1.3
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
     * @since 2.1.3
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoItemCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
