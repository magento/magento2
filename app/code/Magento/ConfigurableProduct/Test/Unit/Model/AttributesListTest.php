<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

class AttributesListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\AttributesList
     */
    protected $attributeListModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeMock;

    protected function setUp(): void
    {
        $this->collectionMock = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class
        );

        /** @var  \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactoryMock */
        $collectionFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
            ['create']
        );
        $collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);

        $methods = ['getId', 'getFrontendLabel', 'getAttributeCode', 'getSource'];
        $this->attributeMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            $methods
        );
        $this->collectionMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn(['id' => $this->attributeMock]);

        $this->attributeListModel = new \Magento\ConfigurableProduct\Model\AttributesList(
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

        $this->attributeMock->expects($this->once())->method('getId')->willReturn('id');
        $this->attributeMock->expects($this->once())->method('getFrontendLabel')->willReturn('label');
        $this->attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('code');

        $source = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class);
        $source->expects($this->once())->method('getAllOptions')->with(false)->willReturn(['options']);
        $this->attributeMock->expects($this->once())->method('getSource')->willReturn($source);

        $this->assertEquals($result, $this->attributeListModel->getAttributes($ids));
    }
}
