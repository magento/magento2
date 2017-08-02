<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;

/**
 * Class CommentCreation
 * @since 2.2.0
 */
class CommentCreation implements CreditmemoCommentCreationInterface
{
    /**
     * @var \Magento\Sales\Api\Data\CreditmemoCommentCreationExtensionInterface
     * @since 2.2.0
     */
    private $extensionAttributes;

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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoCommentCreationExtensionInterface|null
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoCommentCreationExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getIsVisibleOnFront()
    {
        return $this->isVisibleOnFront;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        $this->isVisibleOnFront = $isVisibleOnFront;
        return $this;
    }
}
