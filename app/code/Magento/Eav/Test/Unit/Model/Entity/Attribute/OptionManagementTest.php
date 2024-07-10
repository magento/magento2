<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\Data\AttributeOptionInterface as EavAttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface as EavAttributeOptionLabelInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute as EavAbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\OptionManagement;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table as EavAttributeSource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Eav Option Management functionality
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionManagementTest extends TestCase
{
    /**
     * @var OptionManagement
     */
    protected $model;

    /**
     * @var MockObject|AttributeRepository
     */
    protected $attributeRepositoryMock;

    /**
     * @var MockObject|Attribute
     */
    protected $resourceModelMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(AttributeRepository::class);
        $this->resourceModelMock =
            $this->createMock(Attribute::class);
        $this->model = new OptionManagement(
            $this->attributeRepositoryMock,
            $this->resourceModelMock
        );
    }

    /**
     * Test to add attribute option
     *
     * @param string $label
     * @dataProvider optionLabelDataProvider
     */
    public function testAdd(string $label): void
    {
        $entityType = 42;
        $storeId = 4;
        $attributeCode = 'atrCde';
        $storeLabel = 'labelLabel';
        $sortOder = 'optionSortOrder';
        $option = [
            'value' => [
                'id_new_option' => [
                    0 => $label,
                    $storeId => $storeLabel,
                ],
            ],
            'order' => [
                'id_new_option' => $sortOder,
            ],
            'is_default' => [
                'id_new_option' => true,
            ]
        ];
        $newOptionId = 10;

        $optionMock = $this->getAttributeOption();
        $labelMock = $this->getAttributeOptionLabel();
        /** @var SourceInterface|MockObject $sourceMock */
        $sourceMock = $this->createMock(EavAttributeSource::class);
        $sourceMock->method('getOptionId')
            ->willReturnMap(
                [
                    [$label, null],
                    [$storeLabel, $newOptionId],
                    [$newOptionId, $newOptionId],
                ]
            );

        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(EavAbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['setDefault', 'setOption'])
            ->onlyMethods(['usesSource', 'getSource'])
            ->getMock();
        $attributeMock->method('usesSource')->willReturn(true);
        $attributeMock->expects($this->once())->method('setDefault')->with(['id_new_option']);
        $attributeMock->expects($this->once())->method('setOption')->with($option);
        $attributeMock->method('getSource')->willReturn($sourceMock);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $optionMock->method('getLabel')->willReturn($label);
        $optionMock->method('getSortOrder')->willReturn($sortOder);
        $optionMock->method('getIsDefault')->willReturn(true);
        $optionMock->method('getStoreLabels')->willReturn([$labelMock]);
        $labelMock->method('getStoreId')->willReturn($storeId);
        $labelMock->method('getLabel')->willReturn($storeLabel);
        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock);
        $this->assertEquals(
            $newOptionId,
            $this->model->add($entityType, $attributeCode, $optionMock)
        );
    }

    /**
     * @return array
     */
    public function optionLabelDataProvider(): array
    {
        return [
            ['optionLabel'],
            ['0']
        ];
    }

    /**
     * Test to add attribute option with empty attribute code
     */
    public function testAddWithEmptyAttributeCode()
    {
        $this->expectExceptionMessage("The attribute code is empty. Enter the code and try again.");
        $this->expectException(InputException::class);
        $entityType = 42;
        $attributeCode = '';
        $optionMock = $this->getAttributeOption();
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->add($entityType, $attributeCode, $optionMock);
    }
    /**
     * Test to add attribute option without use source
     */
    public function testAddWithWrongOptions()
    {
        $this->expectExceptionMessage('The "testAttribute" attribute doesn\'t work with options.');
        $this->expectException(StateException::class);
        $entityType = 42;
        $attributeCode = 'testAttribute';
        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(EavAbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['setDefault', 'setOption', 'setStoreId'])
            ->onlyMethods(['usesSource', 'getSource'])
            ->getMock();
        $optionMock = $this->getAttributeOption();
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(false);
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->add($entityType, $attributeCode, $optionMock);
    }

    /**
     * Test to add attribute option wit save exception
     */
    public function testAddWithCannotSaveException()
    {
        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The "atrCde" attribute can\'t be saved.');

        $entityType = 42;
        $storeId = 4;
        $attributeCode = 'atrCde';
        $label = 'optionLabel';
        $storeLabel = 'labelLabel';
        $sortOder = 'optionSortOrder';
        $option = [
            'value' => [
                'id_new_option' => [
                    0 => $label,
                    $storeId => $storeLabel,
                ],
            ],
            'order' => [
                'id_new_option' => $sortOder,
            ],
            'is_default' => [
                'id_new_option' => true,
            ]
        ];

        $optionMock = $this->getAttributeOption();
        $labelMock = $this->getAttributeOptionLabel();
        /** @var SourceInterface|MockObject $sourceMock */
        $sourceMock = $this->createMock(EavAttributeSource::class);
        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(EavAbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['setDefault', 'setOption', 'setStoreId'])
            ->onlyMethods(['usesSource', 'getSource', 'getAttributeCode'])
            ->getMock();
        $attributeMock->method('usesSource')->willReturn(true);
        $attributeMock->expects($this->once())->method('setDefault')->with(['id_new_option']);
        $attributeMock->expects($this->once())->method('setOption')->with($option);
        $attributeMock->method('getSource')->willReturn($sourceMock);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $optionMock->method('getLabel')->willReturn($label);
        $optionMock->method('getSortOrder')->willReturn($sortOder);
        $optionMock->method('getIsDefault')->willReturn(true);
        $optionMock->method('getStoreLabels')->willReturn([$labelMock]);
        $labelMock->method('getStoreId')->willReturn($storeId);
        $labelMock->method('getLabel')->willReturn($storeLabel);

        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock)
            ->willThrowException(new \Exception());
        $this->model->add($entityType, $attributeCode, $optionMock);
    }

    /**
     * Test to update attribute option
     *
     * @param string $label
     * @dataProvider optionLabelDataProvider
     */
    public function testUpdate(string $label): void
    {
        $entityType = Product::ENTITY;
        $storeId = 4;
        $attributeCode = 'atrCde';
        $storeLabel = 'labelLabel';
        $sortOder = 'optionSortOrder';
        $optionId = 10;
        $option = [
            'value' => [
                $optionId => [
                    0 => $label,
                    $storeId => $storeLabel,
                ],
            ],
            'order' => [
                $optionId => $sortOder,
            ],
            'is_default' => [
                $optionId => true,
            ]
        ];

        $optionMock = $this->getAttributeOption();
        $labelMock = $this->getAttributeOptionLabel();
        /** @var SourceInterface|MockObject $sourceMock */
        $sourceMock = $this->createMock(EavAttributeSource::class);

        $sourceMock->expects($this->once())
            ->method('getOptionText')
            ->with($optionId)
            ->willReturn($label);

        $sourceMock->expects($this->once())
            ->method('getOptionId')
            ->with($label)
            ->willReturn($optionId);

        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(EavAbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['setOption'])
            ->onlyMethods(['usesSource', 'getSource'])
            ->getMock();
        $attributeMock->method('usesSource')->willReturn(true);
        $attributeMock->expects($this->once())->method('setOption')->with($option);
        $attributeMock->method('getSource')->willReturn($sourceMock);

        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $optionMock->method('getLabel')->willReturn($label);
        $optionMock->method('getSortOrder')->willReturn($sortOder);
        $optionMock->method('getIsDefault')->willReturn(true);
        $optionMock->method('getStoreLabels')->willReturn([$labelMock]);
        $labelMock->method('getStoreId')->willReturn($storeId);
        $labelMock->method('getLabel')->willReturn($storeLabel);
        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock);

        $this->assertEquals(
            true,
            $this->model->update($entityType, $attributeCode, $optionId, $optionMock)
        );
    }

    /**
     * Test to delete attribute option
     */
    public function testDelete()
    {
        $entityType = 42;
        $attributeCode = 'atrCode';
        $optionId = 'option';

        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(EavAbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOptionText'])
            ->onlyMethods(['usesSource', 'getSource', 'getId', 'addData'])
            ->getMock();
        $removalMarker = [
            'option' => [
                'value' => [$optionId => []],
                'delete' => [$optionId => '1'],
            ],
        ];
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
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
     * Test to delete attribute option with save exception
     */
    public function testDeleteWithCannotSaveException()
    {
        $this->expectExceptionMessage('The "atrCode" attribute can\'t be saved.');
        $this->expectException(StateException::class);

        $entityType = 42;
        $attributeCode = 'atrCode';
        $optionId = 'option';
        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(EavAbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOptionText'])
            ->onlyMethods(['usesSource', 'getSource', 'getId', 'addData'])
            ->getMock();
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
        $this->resourceModelMock->expects($this->once())
            ->method('save')
            ->with($attributeMock)
            ->willThrowException(new \Exception());
        $this->model->delete($entityType, $attributeCode, $optionId);
    }

    /**
     * Test to delete with wrong option
     */
    public function testDeleteWithWrongOption()
    {
        $this->expectExceptionMessage('The "atrCode" attribute doesn\'t include an option with "option" ID.');
        $this->expectException(NoSuchEntityException::class);

        $entityType = 42;
        $attributeCode = 'atrCode';
        $optionId = 'option';
        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createMock(EavAbstractAttribute::class);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $sourceMock = $this->getMockForAbstractClass(SourceInterface::class);
        $sourceMock->expects($this->once())->method('getOptionText')->willReturn(false);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(true);
        $attributeMock->expects($this->once())->method('getSource')->willReturn($sourceMock);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->delete($entityType, $attributeCode, $optionId);
    }

    /**
     * Test to delete with absent option
     */
    public function testDeleteWithAbsentOption()
    {
        $this->expectExceptionMessage('The "atrCode" attribute doesn\'t work with options.');
        $this->expectException(StateException::class);

        $entityType = 42;
        $attributeCode = 'atrCode';
        $optionId = 'option';
        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(EavAbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOptionText'])
            ->onlyMethods(['usesSource', 'getSource', 'getId', 'addData'])
            ->getMock();
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('usesSource')->willReturn(false);
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->delete($entityType, $attributeCode, $optionId);
    }

    /**
     * Test to delete with empty attribute code
     */
    public function testDeleteWithEmptyAttributeCode()
    {
        $this->expectExceptionMessage("The attribute code is empty. Enter the code and try again.");
        $this->expectException(InputException::class);

        $entityType = 42;
        $attributeCode = '';
        $optionId = 'option';
        $this->resourceModelMock->expects($this->never())->method('save');
        $this->model->delete($entityType, $attributeCode, $optionId);
    }

    /**
     * Test to get items
     */
    public function testGetItems()
    {
        $entityType = 42;
        $attributeCode = 'atrCode';
        $attributeMock = $this->createMock(EavAbstractAttribute::class);
        $optionsMock = [$this->createMock(EavAttributeOptionInterface::class)];
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('getOptions')->willReturn($optionsMock);
        $this->assertEquals($optionsMock, $this->model->getItems($entityType, $attributeCode));
    }

    /**
     * Test to get items with load exception
     */
    public function testGetItemsWithCannotLoadException()
    {
        $this->expectExceptionMessage('The options for "atrCode" attribute can\'t be loaded.');
        $this->expectException(StateException::class);
        $entityType = 42;
        $attributeCode = 'atrCode';
        $attributeMock = $this->createMock(EavAbstractAttribute::class);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())
            ->method('getOptions')
            ->willThrowException(new \Exception());
        $this->model->getItems($entityType, $attributeCode);
    }

    /**
     * Test to get items with empty attribute code
     */
    public function testGetItemsWithEmptyAttributeCode()
    {
        $this->expectExceptionMessage("The attribute code is empty. Enter the code and try again.");
        $this->expectException(InputException::class);

        $entityType = 42;
        $attributeCode = '';
        $this->model->getItems($entityType, $attributeCode);
    }

    /**
     * Return attribute option entity mock.
     *
     * @return MockObject|EavAttributeOptionInterface
     */
    private function getAttributeOption()
    {
        return $this->getMockBuilder(EavAttributeOptionInterface::class)
            ->setMethods(['getSourceLabels'])
            ->getMockForAbstractClass();
    }

    /**
     * @return MockObject|EavAttributeOptionLabelInterface
     */
    private function getAttributeOptionLabel()
    {
        return $this->getMockBuilder(EavAttributeOptionLabelInterface::class)
            ->getMockForAbstractClass();
    }
}
