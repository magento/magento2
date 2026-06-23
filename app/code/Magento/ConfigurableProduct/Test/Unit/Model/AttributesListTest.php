<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\ConfigurableProduct\Model\AttributesList;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

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

    protected function setUp(): void
    {
        $this->collectionMock = $this->createMock(
            Collection::class
        );

        /** @var  CollectionFactory $collectionFactoryMock */
        $collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);

        $this->attributeMock = $this->createPartialMockWithReflection(
            Attribute::class,
            ['getId', 'getAttributeCode', 'getFrontendLabel', 'getSource']
        );
        $this->attributeMock->method('getId')->willReturn('id');
        $this->attributeMock->method('getAttributeCode')->willReturn('code');
        $this->attributeMock->method('getFrontendLabel')->willReturn('label');
        $this->collectionMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn(['id' => $this->attributeMock]);

        $this->attributeListModel = new AttributesList(
            $collectionFactoryMock
        );
    }

    public function testGetAttributes()
    {
        $ids = [1];
        $result = [
            [
                'id' => 'id',
                'label' => 'label',
                'code' => 'code',
                'options' => ['options']
            ]
        ];

        $this->collectionMock
            ->expects($this->any())
            ->method('addFieldToFilter')
            ->with('main_table.attribute_id', $ids);

        $source = $this->createMock(AbstractSource::class);
        $source->expects($this->once())->method('getAllOptions')->with(false)->willReturn(['options']);
        $this->attributeMock->method('getSource')->willReturn($source);

        $this->assertEquals($result, $this->attributeListModel->getAttributes($ids));
    }
}
