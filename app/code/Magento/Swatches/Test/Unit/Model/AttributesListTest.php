<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Model\AttributesList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributesListTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var AttributesList
     */
    protected $attributeListModel;

    /**
     * @var Collection|MockObject
     */
    protected $collectionMock;

    /**
     * @var Attribute|MockObject
     */
    protected $attributeMock;

    /** @var Data|MockObject */
    protected $swatchHelper;

    protected function setUp(): void
    {
        $this->swatchHelper = $this->createMock(Data::class);

        $this->collectionMock = $this->createMock(
            Collection::class
        );

        /** @var  CollectionFactory $collectionFactoryMock */
        $collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);

        $sourceMock = $this->createPartialMockWithReflection(\stdClass::class, ['getAllOptions']);
        $sourceMock->method('getAllOptions')->willReturn(['options']);

        $this->attributeMock = $this->createPartialMockWithReflection(
            Attribute::class,
            ['getId', 'getFrontendLabel', 'getAttributeCode', 'getSource']
        );
        $this->attributeMock->method('getId')->willReturn('id');
        $this->attributeMock->method('getFrontendLabel')->willReturn('label');
        $this->attributeMock->method('getAttributeCode')->willReturn('code');
        $this->attributeMock->method('getSource')->willReturn($sourceMock);

        $this->collectionMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn(['id' => $this->attributeMock]);

        $this->attributeListModel = new AttributesList(
            $collectionFactoryMock,
            $this->swatchHelper
        );
    }

    public function testGetAttributes()
    {
        $ids = [1, 2, 3];
        $result = [
            [
                'id' => 'id',
                'label' => 'label',
                'code' => 'code',
                'options' => ['options'],
                'canCreateOption' => false
            ]
        ];

        $this->collectionMock
            ->expects($this->any())
            ->method('addFieldToFilter')
            ->with('main_table.attribute_id', $ids);

        $this->swatchHelper->expects($this->once())->method('isSwatchAttribute')
            ->with($this->attributeMock)
            ->willReturn(true);

        $this->assertEquals($result, $this->attributeListModel->getAttributes($ids));
    }
}
