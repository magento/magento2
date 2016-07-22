<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;

/**
 * Class Comment Creation
 */
class CommentCreation implements InvoiceCommentCreationInterface
{
    /**
     * @var string
     */
    private $comment;

    /**
     * @var int
     */
    private $isVisibleOnFront;

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsVisibleOnFront()
    {
        return $this->isVisibleOnFront;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        $this->isVisibleOnFront = $isVisibleOnFront;
    }
}
