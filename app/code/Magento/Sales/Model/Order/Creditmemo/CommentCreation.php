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
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIsVisibleOnFront()
    {
        return $this->isVisibleOnFront;
    }

    /**
     * @inheritdoc
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        $this->isVisibleOnFront = $isVisibleOnFront;
        return $this;
    }
}
