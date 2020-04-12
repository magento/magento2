<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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

        $methods = ['getId', 'getFrontendLabel', 'getAttributeCode', 'getSource'];
        $this->attributeMock = $this->createPartialMock(
            Attribute::class,
            $methods
        );
        $this->collectionMock
            ->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(['id' => $this->attributeMock]));

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

        $this->attributeMock->expects($this->once())->method('getId')->will($this->returnValue('id'));
        $this->attributeMock->expects($this->once())->method('getFrontendLabel')->will($this->returnValue('label'));
        $this->attributeMock->expects($this->once())->method('getAttributeCode')->will($this->returnValue('code'));

        $source = $this->createMock(AbstractSource::class);
        $source->expects($this->once())->method('getAllOptions')->with(false)->will($this->returnValue(['options']));
        $this->attributeMock->expects($this->once())->method('getSource')->will($this->returnValue($source));

        $this->assertEquals($result, $this->attributeListModel->getAttributes($ids));
    }
}
