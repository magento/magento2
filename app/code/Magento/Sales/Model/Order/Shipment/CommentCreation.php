<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;

/**
 * Class CommentCreation
 * @since 2.1.2
 */
class CommentCreation implements ShipmentCommentCreationInterface
{
    /**
     * @var \Magento\Sales\Api\Data\ShipmentCommentCreationExtensionInterface
     * @since 2.1.2
     */
    private $extensionAttributes;

    /**
     * @var string
     * @since 2.1.2
     */
    private $comment;

    /**
     * @var int
     * @since 2.1.2
     */
    private $isVisibleOnFront;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\ShipmentCommentCreationExtensionInterface|null
     * @since 2.1.2
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\ShipmentCommentCreationExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.1.2
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentCommentCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }

    /**
     * Gets the comment for the invoice.
     *
     * @return string Comment.
     * @since 2.1.2
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Sets the comment for the invoice.
     *
     * @param string $comment
     * @return $this
     * @since 2.1.2
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Gets the is-visible-on-storefront flag value for the invoice.
     *
     * @return int Is-visible-on-storefront flag value.
     * @since 2.1.2
     */
    public function getIsVisibleOnFront()
    {
        return $this->isVisibleOnFront;
    }

    /**
     * Sets the is-visible-on-storefront flag value for the invoice.
     *
     * @param int $isVisibleOnFront
     * @return $this
     * @since 2.1.2
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        $this->isVisibleOnFront = $isVisibleOnFront;
        return $this;
    }
}
