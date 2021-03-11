<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\ResourceModel\Product\Link;

use Magento\GroupedProduct\Model\ResourceModel\Product\Link\RelationPersister;
use Magento\Catalog\Model\ProductLink\LinkFactory;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Link as LinkResourceModel;

class RelationPersisterTest extends \PHPUnit\Framework\TestCase
{
    /** @var RelationPersister|PHPUnit\Framework\MockObject\MockObject */
    private $object;

    /** @var Link */
    private $link;

    /** @var  Relation */
    private $relationProcessor;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var LinkFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $linkFactory;

    /**
     * @var LinkResourceModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->linkFactory = $this->getMockBuilder(LinkFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->relationProcessor = $this->getMockBuilder(Relation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->link = $this->getMockBuilder(Link::class)
            ->setMethods(['getLinkTypeId', 'getProductId', 'getLinkedProductId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->linkFactory->expects($this->any())->method('create')->willReturn($this->link);

        $this->subject = $this->getMockBuilder(LinkResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = $this->objectManager->getObject(
            RelationPersister::class,
            [
                'relationProcessor' => $this->relationProcessor,
                'linkFactory' => $this->linkFactory
            ]
        );
    }

    public function testAfterSaveProductLinks()
    {
        $this->relationProcessor->expects($this->once())->method('addRelation')->with(2, 10);
        $this->assertEquals($this->subject, $this->object->afterSaveProductLinks(
            $this->subject,
            $this->subject,
            2,
            [['product_id' => 10]],
            3
        ));
    }

    public function testAroundDeleteProductLink()
    {
        $subject = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Link::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects($this->any())->method('getIdFieldName')->willReturn('id');
        $subject->expects($this->once())->method('load')->with($this->link, 155, 'id');

        $this->link->expects($this->any())
            ->method('getLinkTypeId')
            ->willReturn(\Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);
        $this->link->expects($this->any())
            ->method('getProductId')
            ->willReturn(12);
        $this->link->expects($this->any())
            ->method('getLinkedProductId')
            ->willReturn(13);

        $this->relationProcessor->expects($this->once())->method('removeRelations')->with(12, 13);
        $this->assertEquals(
            $subject,
            $this->object->aroundDeleteProductLink(
                $subject,
                function () use ($subject) {
                    return $subject;
                },
                155
            )
        );
    }
}
