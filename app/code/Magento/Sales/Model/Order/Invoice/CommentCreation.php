<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;

/**
 * Class InvoiceCommentCreation
 * @since 2.2.0
 */
class CommentCreation implements InvoiceCommentCreationInterface
{

    /**
     * @var string
     * @since 2.2.0
     */
    private $comment;

    /**
     * @var int
     * @since 2.2.0
     */
    private $isVisibleOnFront;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceCommentCreationExtensionInterface
     * @since 2.2.0
     */
    private $extensionAttributes;

    /**
     * Gets the comment for the invoice.
     *
     * @return string Comment.
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        $this->isVisibleOnFront = $isVisibleOnFront;
        return $this;
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\InvoiceCommentCreationExtensionInterface|null
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentCreationExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\InvoiceCommentCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
