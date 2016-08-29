<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;

/**
 * Class CommentCreation
 */
class CommentCreation implements CreditmemoCommentCreationInterface
{
    /**
     * @var \Magento\Sales\Api\Data\CreditmemoCommentCreationExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var int
     */
    private $isVisibleOnFront;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoCommentCreationExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoCommentCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }

    /**
     * Gets the comment for the invoice.
     *
     * @return string Comment.
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
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        $this->isVisibleOnFront = $isVisibleOnFront;
        return $this;
    }
}
