<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

class OptionManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\OptionManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModelMock;

    protected function setUp()
    {
        $this->attributeRepositoryMock = $this->createMock(\Magento\Eav\Model\AttributeRepository::class);
        $this->resourceModelMock =
            $this->createMock(\Magento\Eav\Model\ResourceModel\Entity\Attribute::class);
        $this->model = new \Magento\Eav\Model\Entity\Attribute\OptionManagement(
            $this->attributeRepositoryMock,
            $this->resourceModelMock
        );
    }

    public function testAdd()
    {
        $entityType = 42;
        $attributeCode = 'atrCde';
        $optionMock = $this->getMockForAbstractClass(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getSourceLabels']
        );
        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['usesSource', 'setDefault', 'setOption']
        );
        $labelMock = $this->createMock(\Magento\Eav\Api\Data\AttributeOptionLabelInterface::class);
        $option =
            ['value' => [
                'id_new_option' => [
                    0 => 'optionLabel',
                    42 => 'labelLabel',
                ],
            ],
            'order' => [
                'id_new_option' => 'optionSortOrder',
            ],
            ];

        $this->attributeRepositoryMock->expects($this->once())->method('get')->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(true);
        $optionMock->expects($this->once())->method('getLabel')->willReturn('optionLabel');
        $optionMock->expects($this->once())->method('getSortOrder')->willReturn('optionSortOrder');
        $optionMock->expects($this->exactly(2))->method('getStoreLabels')->willReturn([$labelMock]);
        $labelMock->expects($this->once())->method('getStoreId')->willReturn(42);
        $labelMock->expects($this->once())->method('getLabel')->willReturn('labelLabel');
        $optionMock->expects($this->once())->method('getIsDefault')->willReturn(true);
        $attributeMock->expects($this->once())->method('setDefault')->with(['id_new_option']);
        $attributeMock->expects($this->once())->method('setOption')->with($option);
        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock);
        $this->assertEquals('id_new_option', $this->model->add($entityType, $attributeCode, $optionMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The attribute code is empty. Enter the code and try again.
     */
    public function testAddWithEmptyAttributeCode()
    {
        $entityType = 42;
        $attributeCode = '';
        $optionMock = $this->getMockForAbstractClass(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getSourceLabels']
        );
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->add($entityType, $attributeCode, $optionMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage The "testAttribute" attribute doesn't work with options.
     */
    public function testAddWithWrongOptions()
    {
        $entityType = 42;
        $attributeCode = 'testAttribute';
        $optionMock = $this->getMockForAbstractClass(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getSourceLabels']
        );
        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['usesSource', 'setDefault', 'setOption']
        );
        $this->attributeRepositoryMock->expects($this->once())->method('get')->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(false);
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->add($entityType, $attributeCode, $optionMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage The "atrCde" attribute can't be saved.
     */
    public function testAddWithCannotSaveException()
    {
        $entityType = 42;
        $attributeCode = 'atrCde';
        $optionMock = $this->getMockForAbstractClass(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getSourceLabels']
        );
        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['usesSource', 'setDefault', 'setOption']
        );
        $labelMock = $this->createMock(\Magento\Eav\Api\Data\AttributeOptionLabelInterface::class);
        $option =
            ['value' => [
                'id_new_option' => [
                    0 => 'optionLabel',
                    42 => 'labelLabel',
                ],
            ],
                'order' => [
                    'id_new_option' => 'optionSortOrder',
                ],
            ];

        $this->attributeRepositoryMock->expects($this->once())->method('get')->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(true);
        $optionMock->expects($this->once())->method('getLabel')->willReturn('optionLabel');
        $optionMock->expects($this->once())->method('getSortOrder')->willReturn('optionSortOrder');
        $optionMock->expects($this->exactly(2))->method('getStoreLabels')->willReturn([$labelMock]);
        $labelMock->expects($this->once())->method('getStoreId')->willReturn(42);
        $labelMock->expects($this->once())->method('getLabel')->willReturn('labelLabel');
        $optionMock->expects($this->once())->method('getIsDefault')->willReturn(true);
        $attributeMock->expects($this->once())->method('setDefault')->with(['id_new_option']);
        $attributeMock->expects($this->once())->method('setOption')->with($option);
        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock)
            ->willThrowException(new \Exception());
        $this->model->add($entityType, $attributeCode, $optionMock);
    }

    public function testDelete()
    {
        $entityType = 42;
        $attributeCode = 'atrCode';
        $optionId = 'option';
        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['usesSource', 'getSource', 'getId', 'getOptionText', 'addData']
        );
        $removalMarker = [
            'option' => [
                'value' => [$optionId => []],
                'delete' => [$optionId => '1'],
            ],
        ];
        $this->attributeRepositoryMock->expects($this->once())->method('get')->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(true);
        $attributeMock->expects($this->once())->method('getSource')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getOptionText')->willReturn('optionText');
        $attributeMock->expects($this->never())->method('getId');
        $attributeMock->expects($this->once())->method('addData')->with($removalMarker);
        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock);
        $this->assertTrue($this->model->delete($entityType, $attributeCode, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage The "atrCode" attribute can't be saved.
     */
    public function testDeleteWithCannotSaveException()
    {
        $entityType = 42;
        $attributeCode = 'atrCode';
        $optionId = 'option';
        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['usesSource', 'getSource', 'getId', 'getOptionText', 'addData']
        );
        $removalMarker = [
            'option' => [
                'value' => [$optionId => []],
                'delete' => [$optionId => '1'],
            ],
        ];
        $this->attributeRepositoryMock->expects($this->once())->method('get')->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(true);
        $attributeMock->expects($this->once())->method('getSource')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getOptionText')->willReturn('optionText');
        $attributeMock->expects($this->never())->method('getId');
        $attributeMock->expects($this->once())->method('addData')->with($removalMarker);
        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock)
        ->willThrowException(new \Exception());
        $this->model->delete($entityType, $attributeCode, $optionId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage The "atrCode" attribute doesn't include an option with "option" ID.
     */
    public function testDeleteWithWrongOption()
    {
        $entityType = 42;
        $attributeCode = 'atrCode';
        $optionId = 'option';
        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['usesSource', 'getSource', 'getAttributeCode']
        );
        $this->attributeRepositoryMock->expects($this->once())->method('get')->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $sourceMock = $this->getMockForAbstractClass(\Magento\Eav\Model\Entity\Attribute\Source\SourceInterface::class);
        $sourceMock->expects($this->once())->method('getOptionText')->willReturn(false);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(true);
        $attributeMock->expects($this->once())->method('getSource')->willReturn($sourceMock);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->delete($entityType, $attributeCode, $optionId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage The "atrCode" attribute has no option.
     */
    public function testDeleteWithAbsentOption()
    {
        $entityType = 42;
        $attributeCode = 'atrCode';
        $optionId = 'option';
        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['usesSource', 'getSource', 'getId', 'getOptionText', 'addData']
        );
        $this->attributeRepositoryMock->expects($this->once())->method('get')->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(false);
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->delete($entityType, $attributeCode, $optionId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The attribute code is empty. Enter the code and try again.
     */
    public function testDeleteWithEmptyAttributeCode()
    {
        $entityType = 42;
        $attributeCode = '';
        $optionId = 'option';
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->delete($entityType, $attributeCode, $optionId);
    }

    public function testGetItems()
    {
        $entityType = 42;
        $attributeCode = 'atrCode';
        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['getOptions']
        );
        $optionsMock = [$this->createMock(\Magento\Eav\Api\Data\AttributeOptionInterface::class)];
        $this->attributeRepositoryMock->expects($this->once())->method('get')->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('getOptions')->willReturn($optionsMock);
        $this->assertEquals($optionsMock, $this->model->getItems($entityType, $attributeCode));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage The options for "atrCode" attribute can't be loaded.
     */
    public function testGetItemsWithCannotLoadException()
    {
        $entityType = 42;
        $attributeCode = 'atrCode';
        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['getOptions']
        );
        $this->attributeRepositoryMock->expects($this->once())->method('get')->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('getOptions')->willThrowException(new \Exception());
        $this->model->getItems($entityType, $attributeCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The attribute code is empty. Enter the code and try again.
     */
    public function testGetItemsWithEmptyAttributeCode()
    {
        $entityType = 42;
        $attributeCode = '';
        $this->model->getItems($entityType, $attributeCode);
    }
}
