<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class Relation
 */
class Relation implements RelationInterface
{
    /**
     * @var Item
     */
    protected $creditmemoItemResource;

    /**
     * @var Comment
     */
    protected $creditmemoCommentResource;

    /**
     * @param Item $creditmemoItemResource
     * @param Comment $creditmemoCommentResource
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
