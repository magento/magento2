<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

class AttributesListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\AttributesList
     */
    protected $attributeListModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;


    protected function setUp()
    {
        $this->collectionMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection',
            [],
            [],
            '',
            false
        );

        /** @var  \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactoryMock */
        $collectionFactoryMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);

        $methods = ['getId', 'getFrontendLabel', 'getAttributeCode', 'getSource'];
        $this->attributeMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            $methods,
            [],
            '',
            false
        );
        $this->collectionMock
            ->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(['id' => $this->attributeMock]));

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

        $this->attributeMock->expects($this->once())->method('getId')->will($this->returnValue('id'));
        $this->attributeMock->expects($this->once())->method('getFrontendLabel')->will($this->returnValue('label'));
        $this->attributeMock->expects($this->once())->method('getAttributeCode')->will($this->returnValue('code'));

        $source = $this->getMock('Magento\Eav\Model\Entity\Attribute\Source\AbstractSource', [], [], '', false);
        $source->expects($this->once())->method('getAllOptions')->with(false)->will($this->returnValue(['options']));
        $this->attributeMock->expects($this->once())->method('getSource')->will($this->returnValue($source));

        $this->assertEquals($result, $this->attributeListModel->getAttributes($ids));
    }
}
