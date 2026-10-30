<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\ResourceModel\Product\Link;

use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\ProductLink\LinkFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link as LinkResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link\RelationPersister;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RelationPersisterTest extends TestCase
{
    use MockCreationTrait;
    /** @var RelationPersister|MockObject */
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
     * @var LinkFactory|MockObject
     */
    private $linkFactory;

    /**
     * @var LinkResourceModel|MockObject
     */
    private $subject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->linkFactory = $this->createPartialMock(LinkFactory::class, ['create']);

        $this->relationProcessor = $this->createMock(Relation::class);

        $this->link = $this->createPartialMockWithReflection(
            \Magento\Catalog\Model\Product\Link::class,
            ['getLinkTypeId', 'getProductId', 'getLinkedProductId']
        );

        $this->linkFactory->method('create')->willReturn($this->link);

        $this->subject = $this->createMock(LinkResourceModel::class);

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
        $subject = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Link::class,
            ['getIdFieldName', 'load']
        );
        $subject->method('getIdFieldName')->willReturn('id');
        $subject->expects($this->once())->method('load')->with($this->link, 155, 'id');

        // Configure the mock to return the expected values when getters are called
        $this->link->method('getLinkTypeId')
            ->willReturn(\Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);
        $this->link->method('getProductId')->willReturn(12);
        $this->link->method('getLinkedProductId')->willReturn(13);

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
