<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

class AttributesListTest extends TestCase
{
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

        $this->attributeMock = $this->getMockBuilder(Attribute::class)
            ->addMethods(['getFrontendLabel'])
            ->onlyMethods(['getId', 'getAttributeCode', 'getSource'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $this->attributeMock->expects($this->once())->method('getId')->willReturn('id');
        $this->attributeMock->expects($this->once())->method('getFrontendLabel')->willReturn('label');
        $this->attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('code');

        $source = $this->createMock(AbstractSource::class);
        $source->expects($this->once())->method('getAllOptions')->with(false)->willReturn(['options']);
        $this->attributeMock->expects($this->once())->method('getSource')->willReturn($source);

        $this->assertEquals($result, $this->attributeListModel->getAttributes($ids));
    }
}
