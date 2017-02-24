<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\AttributeSetsFixture;

/**
 * @SuppressWarnings(PHPMD)
 */
class AttributeSetsFixtureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\AttributeSetsFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder(\Magento\Setup\Fixtures\FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AttributeSetsFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $attributeSets = [
            'attribute_set' => [
                [
                    'name' => 'attribute set name',
                    'attributes' => [
                        'attribute' => [
                            [
                                'is_required' => 1,
                                'is_visible_on_front' => 1,
                                'is_visible_in_advanced_search' => 1,
                                'is_filterable' => 1,
                                'is_filterable_in_search' => 1,
                                'default_value' => 'yellow1',
                                'attribute_code' => 'mycolor',
                                'is_searchable' => '1',
                                'frontend_label' => 'mycolor',
                                'frontend_input' => 'select',
                                'options' => [
                                    'option' => [
                                        [
                                            'label' => 'yellow1',
                                            'value' => ''
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $attributeSet = $attributeSets['attribute_set'][0];

        // Mock Attribute Sets
        $attributeSetMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeSetInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetMock->expects($this->once())
            ->method('setAttributeSetName')
            ->with("attribute set name");
        $attributeSetMock->expects($this->once())
            ->method('setEntityTypeId')
            ->with(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE);
        $attributeSetMock->expects($this->any())
            ->method('getAttributeSetName')
            ->willReturn($attributeSet['name']);

        $attributeSetFactoryMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeSetInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $attributeSetFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($attributeSetMock);

        $attributeSetManagementMock = $this->getMockBuilder(\Magento\Catalog\Api\AttributeSetManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetManagementMock->expects($this->once())
            ->method('create')
            ->with($attributeSetMock, '4')
            ->willReturn($attributeSetMock);

        //Mock Attribute Groups
        $attributeGroupMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeGroupInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeGroupMock->expects($this->once())
            ->method('setAttributeGroupName')
            ->with($attributeSetMock->getAttributeSetName() . ' - Group');
        $attributeGroupMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with($attributeSetMock->getAttributeSetId());

        $attributeGroupFactoryMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeGroupInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $attributeGroupFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($attributeGroupMock);

        $productAttributeGroupRepoMock = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $productAttributeGroupRepoMock->expects($this->once())
            ->method('save')
            ->with($attributeGroupMock)
            ->willReturn($attributeGroupMock);

        // Mock Attributes
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attributeFactoryMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $attributeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($attributeMock);

        //Mock Attribute Options
        $optionMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeOptionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $optionFactoryMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeOptionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $productAttributeRepoMock = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductAttributeRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $productAttributeRepoMock->expects($this->once())
            ->method('save')
            ->with($attributeMock)
            ->willReturn($attributeMock);

        $productAttributeManagementMock = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductAttributeManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $productAttributeManagementMock->expects($this->once())
            ->method('assign')
            ->willReturn($attributeMock->getAttributeId());

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->at(0))
            ->method('create')
            ->willReturn($attributeSetManagementMock);
        $objectManagerMock->expects($this->at(1))
            ->method('create')
            ->willReturn($productAttributeGroupRepoMock);
        $objectManagerMock->expects($this->at(2))
            ->method('create')
            ->willReturn($attributeSetFactoryMock);
        $objectManagerMock->expects($this->at(3))
            ->method('create')
            ->willReturn($attributeGroupFactoryMock);
        $objectManagerMock->expects($this->at(4))
            ->method('create')
            ->willReturn($productAttributeRepoMock);
        $objectManagerMock->expects($this->at(5))
            ->method('create')
            ->willReturn($productAttributeManagementMock);
        $objectManagerMock->expects($this->at(6))
            ->method('create')
            ->willReturn($attributeFactoryMock);
        $objectManagerMock->expects($this->at(7))
            ->method('create')
            ->willReturn($optionFactoryMock);

        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturn($attributeSets);

        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $attributeSetManagementMock = $this->getMockBuilder(\Magento\Catalog\Api\AttributeSetManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetManagementMock->expects($this->never())->method('create');

        $productAttributeGroupRepoMock = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $productAttributeGroupRepoMock->expects($this->never())->method('save');

        $productAttributeRepoMock = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductAttributeRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $productAttributeRepoMock->expects($this->never())->method('save');

        $productAttributeManagementMock = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductAttributeManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $productAttributeManagementMock->expects($this->never())->method('assign');

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo(\Magento\Catalog\Api\AttributeSetManagementInterface::class))
            ->willReturn($attributeSetManagementMock);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo(\Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface::class))
            ->willReturn($productAttributeGroupRepoMock);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class))
            ->willReturn($productAttributeRepoMock);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with($this->equalTo(\Magento\Catalog\Api\ProductAttributeManagementInterface::class))
            ->willReturn($productAttributeManagementMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));
        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturn(null);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating attribute sets', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'attribute_sets' => 'Attribute Sets'
        ], $this->model->introduceParamLabels());
    }
}
