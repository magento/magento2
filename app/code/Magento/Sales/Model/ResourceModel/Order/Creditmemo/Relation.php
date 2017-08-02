<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class Relation
 * @since 2.0.0
 */
class Relation implements RelationInterface
{
    /**
     * @var Item
     * @since 2.0.0
     */
    protected $creditmemoItemResource;

    /**
     * @var Comment
     * @since 2.0.0
     */
    protected $creditmemoCommentResource;

    /**
     * @param Item $creditmemoItemResource
     * @param Comment $creditmemoCommentResource
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item $creditmemoItemResource,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment $creditmemoCommentResource
    ) {
        $this->creditmemoItemResource = $creditmemoItemResource;
        $this->creditmemoCommentResource = $creditmemoCommentResource;
    }

    /**
     * Process relations for CreditMemo
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @throws \Exception
     * @return void
     * @since 2.0.0
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $object */
        if (null !== $object->getItems()) {
            foreach ($object->getItems() as $item) {
                $item->setParentId($object->getId());
                $this->creditmemoItemResource->save($item);
            }
        }
        if (null !== $object->getComments()) {
            foreach ($object->getComments() as $comment) {
                $this->creditmemoCommentResource->save($comment);
            }
        }
    }
}
