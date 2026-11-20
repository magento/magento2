<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option as AttributeOptionResource;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Tests for Eav Option Management functionality
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionManagementTest extends TestCase
{
    use MockCreationTrait;

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
     * @var MockObject|AttributeOptionResource
     */
    protected $optionResourceMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(AttributeRepository::class);
        $this->resourceModelMock = $this->createMock(Attribute::class);
        $this->optionResourceMock =  $this->createMock(AttributeOptionResource::class);
        $this->model = new OptionManagement(
            $this->attributeRepositoryMock,
            $this->resourceModelMock,
            $this->optionResourceMock
        );
    }

    /**
     * Test to add attribute option
     *
     * @param string $label
     */
    #[DataProvider('optionLabelDataProvider')]
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
        // The new option's admin label is not resolvable, so the id is retrieved via its store label.
        $sourceMock->method('getAllOptions')
            ->willReturn([['value' => (string)$newOptionId, 'label' => $storeLabel]]);

        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['setDefault', 'setOption', 'usesSource', 'getSource']
        );
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
    public static function optionLabelDataProvider(): array
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
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['setDefault', 'setOption', 'setStoreId', 'usesSource', 'getSource']
        );
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
        $sourceMock->method('getAllOptions')->willReturn([]);
        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['setDefault', 'setOption', 'setStoreId', 'usesSource', 'getSource', 'getAttributeCode']
        );
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
     */
    #[DataProvider('optionLabelDataProvider')]
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
                    5 => 'otherLabelLabel'
                ],
            ],
            'order' => [
                $optionId => $sortOder,
            ],
            'is_default' => [
                $optionId => true,
            ]
        ];

        $this->optionResourceMock->expects($this->once())
            ->method('getStoreLabelsByOptionId')
            ->with($optionId)
            ->willReturn([
                4 => 'oldLabelLabel',
                5 => 'otherLabelLabel'
            ]);

        $optionMock = $this->getAttributeOption();
        $labelMock1 = $this->getAttributeOptionLabel();
        $labelMock2 = $this->getAttributeOptionLabel();
        /** @var SourceInterface|MockObject $sourceMock */
        $sourceMock = $this->createMock(EavAttributeSource::class);

        $sourceMock->expects($this->once())
            ->method('getOptionText')
            ->with($optionId)
            ->willReturn($label);

        $sourceMock->method('getAllOptions')
            ->willReturn([['value' => (string)$optionId, 'label' => $label]]);

        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['setOption', 'usesSource', 'getSource']
        );
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
        $optionMock->method('getStoreLabels')->willReturn([$labelMock1, $labelMock2]);
        $labelMock1->method('getStoreId')->willReturn($storeId);
        $labelMock1->method('getLabel')->willReturn($storeLabel);
        $labelMock2->method('getStoreId')->willReturn(5);
        $labelMock2->method('getLabel')->willReturn('otherLabelLabel');
        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock);

        $this->assertEquals(
            true,
            $this->model->update($entityType, $attributeCode, $optionId, $optionMock)
        );
    }

    /**
     * A new option whose label coincides with an existing option's id (value) must be added successfully.
     *
     * Regression for ACP2E-5107: the duplicate check must compare labels only, not option ids.
     */
    public function testAddOptionWithLabelMatchingExistingOptionId(): void
    {
        $entityType = 42;
        $attributeCode = 'atrCde';
        $label = '13';
        $newOptionId = 20;

        $optionMock = $this->getAttributeOption();
        $optionMock->method('getLabel')->willReturn($label);
        $optionMock->method('getStoreLabels')->willReturn([]);

        /** @var SourceInterface|MockObject $sourceMock */
        $sourceMock = $this->createMock(EavAttributeSource::class);
        // Existing option "optionA" has the value/id 13 - it must NOT collide with the new label "13".
        $optionsBeforeSave = [
            ['value' => '', 'label' => ' '],
            ['value' => '13', 'label' => 'optionA'],
        ];
        // After save the freshly created option (label "13") appears with its own distinct id.
        $optionsAfterSave = array_merge(
            $optionsBeforeSave,
            [['value' => (string)$newOptionId, 'label' => $label]]
        );
        $sourceMock->method('getAllOptions')
            ->willReturnOnConsecutiveCalls($optionsBeforeSave, $optionsAfterSave);
        // Mirror the real source's label-or-value lookup so this test fails if the old logic returns.
        $sourceMock->method('getOptionId')->willReturn(13);
        $this->optionResourceMock->method('getStoreLabelsByOptionId')->willReturn([]);

        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['setOption', 'usesSource', 'getSource']
        );
        $attributeMock->method('usesSource')->willReturn(true);
        $attributeMock->method('getSource')->willReturn($sourceMock);
        $attributeMock->expects($this->once())->method('setOption');
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock);

        $this->assertEquals(
            (string)$newOptionId,
            $this->model->add($entityType, $attributeCode, $optionMock)
        );
    }

    /**
     * A genuine duplicate label (case-insensitive) must still be rejected.
     */
    public function testAddOptionWithDuplicateLabelThrows(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Admin store attribute option label "OPTIONA" already exists.');

        $entityType = 42;
        $attributeCode = 'atrCde';
        $label = 'OPTIONA';

        $optionMock = $this->getAttributeOption();
        $optionMock->method('getLabel')->willReturn($label);

        /** @var SourceInterface|MockObject $sourceMock */
        $sourceMock = $this->createMock(EavAttributeSource::class);
        $sourceMock->method('getAllOptions')->willReturn([['value' => '5', 'label' => 'optionA']]);
        // Mirror the real source's label-or-value lookup so this guard also holds against the old logic.
        $sourceMock->method('getOptionId')->willReturn('5');

        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['usesSource', 'getSource']
        );
        $attributeMock->method('usesSource')->willReturn(true);
        $attributeMock->method('getSource')->willReturn($sourceMock);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $this->resourceModelMock->expects($this->never())->method('save');

        $this->model->add($entityType, $attributeCode, $optionMock);
    }

    /**
     * Updating an option to a label that coincides with a different option's id (value) must succeed.
     *
     * Regression for ACP2E-5107 on the update path.
     */
    public function testUpdateOptionWithLabelMatchingOtherOptionId(): void
    {
        $entityType = Product::ENTITY;
        $attributeCode = 'atrCde';
        $optionId = 10;
        $label = '13';

        $optionMock = $this->getAttributeOption();
        $optionMock->method('getLabel')->willReturn($label);
        $optionMock->method('getStoreLabels')->willReturn([]);
        $this->optionResourceMock->method('getStoreLabelsByOptionId')->willReturn([]);

        /** @var SourceInterface|MockObject $sourceMock */
        $sourceMock = $this->createMock(EavAttributeSource::class);
        $sourceMock->expects($this->once())
            ->method('getOptionText')
            ->with($optionId)
            ->willReturn('Current Label');
        // Option with value/id 13 exists but under a different label - must not block the update.
        $sourceMock->method('getAllOptions')->willReturn(
            [
                ['value' => '13', 'label' => 'optionA'],
                ['value' => '10', 'label' => 'Current Label'],
            ]
        );
        // Mirror the real source's label-or-value lookup: the old logic would resolve "13" to option id 13
        // (a different option) and wrongly block the update - this test must fail if that logic returns.
        $sourceMock->method('getOptionId')->willReturn(13);

        /** @var EavAbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['setOption', 'usesSource', 'getSource']
        );
        $attributeMock->method('usesSource')->willReturn(true);
        $attributeMock->method('getSource')->willReturn($sourceMock);
        $attributeMock->expects($this->once())->method('setOption');
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityType, $attributeCode)
            ->willReturn($attributeMock);
        $this->resourceModelMock->expects($this->once())->method('save')->with($attributeMock);

        $this->assertTrue(
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
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['getOptionText', 'usesSource', 'getSource', 'getId', 'addData']
        );
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
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['getOptionText', 'usesSource', 'getSource', 'getId', 'addData']
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
        $sourceMock = $this->createMock(SourceInterface::class);
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
        $attributeMock = $this->createPartialMockWithReflection(
            EavAbstractAttribute::class,
            ['getOptionText', 'usesSource', 'getSource', 'getId', 'addData']
        );
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
        return $this->createMock(EavAttributeOptionInterface::class);
    }

    /**
     * @return MockObject|EavAttributeOptionLabelInterface
     */
    private function getAttributeOptionLabel()
    {
        return $this->createMock(EavAttributeOptionLabelInterface::class);
    }
}
