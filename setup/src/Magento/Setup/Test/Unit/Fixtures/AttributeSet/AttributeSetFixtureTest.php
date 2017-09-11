<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures\AttributeSet;

/**
 * @SuppressWarnings(PHPMD)
 */
class AttributeSetFixtureTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $attributeSets = [
            'name' => 'attribute set name',
            'attributes' => [
                'attribute' => [
                    [
                        'is_required' => 1,
                        'is_visible_on_front' => 1,
                        'is_visible_in_advanced_search' => 0,
                        'is_filterable' => 0,
                        'is_filterable_in_search' => 0,
                        'attribute_code' => 'attribute_1',
                        'is_searchable' => 0,
                        'frontend_label' => 'Attribute 1',
                        'frontend_input' => 'select',
                        'backend_type' => 1,
                        'default_option' => 'option 1',
                        'options' => [
                            'option' => [
                                [
                                    'label' => 'option 1',
                                    'value' => 'option_1'
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];

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
            ->willReturn($attributeSets['name']);

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

        $attributeSet = new \Magento\Setup\Fixtures\AttributeSet\AttributeSetFixture(
            $attributeSetManagementMock,
            $productAttributeGroupRepoMock,
            $productAttributeRepoMock,
            $productAttributeManagementMock,
            $attributeFactoryMock,
            $optionFactoryMock,
            $attributeSetFactoryMock,
            $attributeGroupFactoryMock
        );
        $attributeSet->createAttributeSet($attributeSets);
    }
}
