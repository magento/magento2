<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Shipment;

use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Comment as CommentEntity;
use Magento\Sales\Model\Order\Shipment\Item as ItemEntity;
use Magento\Sales\Model\Order\Shipment\Track as TrackEntity;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Item;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Relation;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class RelationTest
 */
class RelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Relation
     */
    private $relationProcessor;

    /**
     * @var Item|MockObject
     */
    private $itemResource;

    /**
     * @var Track|MockObject
     */
    private $trackResource;

    /**
     * @var Comment|MockObject
     */
    private $commentResource;

    /**
     * @var CommentEntity|MockObject
     */
    private $comment;

    /**
     * @var TrackEntity|MockObject
     */
    private $track;

    /**
     * @var Shipment|MockObject
     */
    private $shipment;

    /**
     * @var ItemEntity|MockObject
     */
    private $item;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->itemResource = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentResource = $this->getMockBuilder(Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->trackResource = $this->getMockBuilder(Track::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->item = $this->getMockBuilder(ItemEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->track = $this->getMockBuilder(TrackEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->comment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->relationProcessor = new Relation(
            $this->itemResource,
            $this->trackResource,
            $this->commentResource
        );
    }

    /**
     * Checks saving shipment relations.
     *
     * @throws \Exception
     */
    public function testProcessRelations()
    {
        $this->shipment->method('getId')
            ->willReturn('shipment-id-value');
        $this->shipment->method('getItems')
            ->willReturn([$this->item]);
        $this->shipment->method('getComments')
            ->willReturn([$this->comment]);
        $this->shipment->method('getTracks')
            ->willReturn([$this->track]);
        $this->item->method('setParentId')
            ->with('shipment-id-value')
            ->willReturnSelf();
        $this->itemResource->method('save')
            ->with($this->item)
            ->willReturnSelf();
        $this->commentResource->method('save')
            ->with($this->comment)
            ->willReturnSelf();
        $this->trackResource->method('save')
            ->with($this->track)
            ->willReturnSelf();
        $this->relationProcessor->processRelation($this->shipment);
    }
}
