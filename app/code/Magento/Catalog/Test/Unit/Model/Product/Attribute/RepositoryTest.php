<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\FrontendLabel;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filter\FilterManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Eav\Model\Validator\Attribute\Code;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @var Repository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $attributeResourceMock;

    /**
     * @var MockObject
     */
    protected $productHelperMock;

    /**
     * @var MockObject
     */
    protected $filterManagerMock;

    /**
     * @var MockObject
     */
    protected $eavAttributeRepositoryMock;

    /**
     * @var MockObject
     */
    protected $eavConfigMock;

    /**
     * @var MockObject
     */
    protected $validatorFactoryMock;

    /**
     * @var MockObject
     */
    protected $metadataConfigMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var MockObject
     */
    protected $searchResultMock;

    /**
     * @var MockObject
     */
    protected $attributeCodeValidatorMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeResourceMock =
            $this->createMock(\Magento\Catalog\Model\ResourceModel\Attribute::class);
        $this->productHelperMock =
            $this->createMock(Product::class);
        $this->filterManagerMock =
            $this->getMockBuilder(FilterManager::class)
                ->disableOriginalConstructor()
                ->addMethods(['translitUrl'])
                ->getMock();
        $this->eavAttributeRepositoryMock =
            $this->createMock(AttributeRepositoryInterface::class);
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->eavConfigMock->method('getEntityType')->willReturn(new DataObject(['default_attribute_set_id' => 4]));
        $this->validatorFactoryMock = $this->createPartialMock(
            ValidatorFactory::class,
            ['create']
        );
        $this->searchCriteriaBuilderMock =
            $this->createMock(SearchCriteriaBuilder::class);
        $this->searchResultMock =
            $this->createMock(SearchResultsInterface::class);

        $this->attributeCodeValidatorMock = $this->createMock(Code::class);
        $this->attributeCodeValidatorMock
            ->method('isValid')
            ->willReturn(true);

        $this->model = new Repository(
            $this->attributeResourceMock,
            $this->productHelperMock,
            $this->filterManagerMock,
            $this->eavAttributeRepositoryMock,
            $this->eavConfigMock,
            $this->validatorFactoryMock,
            $this->searchCriteriaBuilderMock,
            $this->attributeCodeValidatorMock
        );
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $attributeCode = 'some attribute code';
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            );
        $this->model->get($attributeCode);
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $searchCriteriaMock
            );

        $this->model->getList($searchCriteriaMock);
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $attributeMock = $this->createMock(Attribute::class);
        $this->attributeResourceMock->expects($this->once())->method('delete')->with($attributeMock);

        $this->assertTrue($this->model->delete($attributeMock));
    }

    /**
     * @return void
     */
    public function testDeleteById()
    {
        $attributeCode = 'some attribute code';
        $attributeMock = $this->createMock(Attribute::class);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            )->willReturn($attributeMock);
        $this->attributeResourceMock->expects($this->once())->method('delete')->with($attributeMock);

        $this->assertTrue($this->model->deleteById($attributeCode));
    }

    /**
     * @return void
     */
    public function testGetCustomAttributesMetadata()
    {
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $itemMock = $this->createMock(ProductInterface::class);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $searchCriteriaMock
            )->willReturn($this->searchResultMock);
        $this->searchResultMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);
        $expected = [$itemMock];

        $this->assertEquals($expected, $this->model->getCustomAttributesMetadata());
    }

    public function testSaveNoSuchEntityException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity with attribute_code = test attribute code');
        $attributeMock = $this->createMock(Attribute::class);
        $existingModelMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn('12');
        $attributeCode = 'test attribute code';
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            )
            ->willReturn($existingModelMock);
        $existingModelMock->expects($this->once())->method('getAttributeId')->willReturn(null);
        $existingModelMock->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);

        $this->model->save($attributeMock);
    }

    public function testSaveInputExceptionRequiredField()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('"frontend_label" is required. Enter and try again.');
        $attributeMock = $this->createPartialMock(
            Attribute::class,
            ['getFrontendLabels', 'getDefaultFrontendLabel', 'getAttributeId', 'setAttributeId']
        );
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $attributeMock->expects($this->once())->method('getFrontendLabels')->willReturn(null);
        $attributeMock->expects($this->once())->method('getDefaultFrontendLabel')->willReturn(null);

        $this->model->save($attributeMock);
    }

    /**
     * @param string $field
     * @param string $method
     * @param bool $filterable
     *
     * @return void
     */
    #[DataProvider('filterableDataProvider')]
    public function testSaveInputExceptionInvalidIsFilterableFieldValue(
        string $field,
        string $method,
        bool $filterable
    ) : void {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Invalid value of "'.$filterable.'" provided for the '.$field.' field.');
        $attributeMock = $this->createPartialMock(
            Attribute::class,
            ['getFrontendInput', $method]
        );
        $attributeMock->expects($this->atLeastOnce())->method('getFrontendInput')->willReturn('text');
        $attributeMock->expects($this->atLeastOnce())->method($method)->willReturn($filterable);

        $this->model->save($attributeMock);
    }

    /**
     * @return array
     */
    public static function filterableDataProvider(): array
    {
        return [
            [ProductAttributeInterface::IS_FILTERABLE, 'getIsFilterable', true],
            [ProductAttributeInterface::IS_FILTERABLE_IN_SEARCH, 'getIsFilterableInSearch', true]
        ];
    }

    public function testSaveInputExceptionInvalidFieldValue()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Invalid value of "" provided for the frontend_label field.');
        $attributeMock = $this->createPartialMock(
            Attribute::class,
            ['getFrontendLabels', 'getDefaultFrontendLabel', 'getAttributeId', 'setAttributeId']
        );
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $labelMock = $this->createMock(FrontendLabel::class);
        $attributeMock->method('getFrontendLabels')->willReturn([$labelMock]);
        $attributeMock->method('getDefaultFrontendLabel')->willReturn(null);
        $labelMock->expects($this->once())->method('getStoreId')->willReturn(0);
        $labelMock->expects($this->once())->method('getLabel')->willReturn(null);

        $this->model->save($attributeMock);
    }

    /**
     * @return void
     */
    public function testSaveDoesNotSaveAttributeOptionsIfOptionsAreAbsentInPayload()
    {
        $attributeId = 1;
        $attributeCode = 'existing_attribute_code';
        $backendModel = 'backend_model';
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);
        $attributeMock->method('getAttributeId')->willReturn($attributeId);
        $attributeMock->expects($this->once())->method('setBackendModel')->with($backendModel)->willReturnSelf();

        $existingModelMock = $this->createMock(Attribute::class);
        $existingModelMock->method('getAttributeCode')->willReturn($attributeCode);
        $existingModelMock->method('getAttributeId')->willReturn($attributeId);
        $existingModelMock->expects($this->once())->method('getBackendModel')->willReturn($backendModel);

        $this->eavAttributeRepositoryMock->expects($this->any())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($existingModelMock);
        $existingModelMock->expects($this->once())->method('getDefaultFrontendLabel')->willReturn('default_label');
        // Attribute code must not be changed after attribute creation
        $attributeMock->expects($this->once())->method('setAttributeCode')->with($attributeCode);
        $this->attributeResourceMock->expects($this->once())->method('save')->with($attributeMock);

        $this->model->save($attributeMock);
    }

    /**
     * @return void
     */
    public function testSaveSavesDefaultFrontendLabelIfItIsPresentInPayload()
    {
        $backendModel = 'backend_model';
        $labelMock = $this->createMock(AttributeFrontendLabelInterface::class);
        $labelMock->method('getStoreId')->willReturn(1);
        $labelMock->method('getLabel')->willReturn('Store Scope Label');

        $attributeId = 1;
        $attributeCode = 'existing_attribute_code';
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);
        $attributeMock->method('getAttributeId')->willReturn($attributeId);
        $attributeMock->method('getDefaultFrontendLabel')->willReturn(null);
        $attributeMock->method('getFrontendLabels')->willReturn([$labelMock]);
        $attributeMock->method('getOptions')->willReturn([]);
        $attributeMock->expects($this->once())->method('setBackendModel')->with($backendModel)->willReturnSelf();

        $existingModelMock = $this->createMock(Attribute::class);
        $existingModelMock->method('getDefaultFrontendLabel')->willReturn('Default Label');
        $existingModelMock->method('getAttributeId')->willReturn($attributeId);
        $existingModelMock->method('getAttributeCode')->willReturn($attributeCode);
        $existingModelMock->expects($this->once())->method('getBackendModel')->willReturn($backendModel);

        $this->eavAttributeRepositoryMock->expects($this->any())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($existingModelMock);

        $attributeMock->expects($this->once())
            ->method('setDefaultFrontendLabel')
            ->with('Default Label');
        $this->attributeResourceMock->expects($this->once())->method('save')->with($attributeMock);

        $this->model->save($attributeMock);
    }

    /**
     * @return void
     */
    public function testSaveInputExceptionInvalidBackendType()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Invalid value of "decimal" provided for the backend_type field.');
        $attributeMock = $this->createPartialMock(
            Attribute::class,
            [
                'getFrontendLabels',
                'getDefaultFrontendLabel',
                'getAttributeId',
                'setAttributeId',
                'getAttributeCode',
                'getBackendTypeByInput',
                'getBackendType'
            ]
        );
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $labelMock = $this->createMock(FrontendLabel::class);
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([$labelMock]);
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn('default_label');
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('attribute_code');
        $attributeMock->expects($this->any())->method('getBackendTypeByInput')->willReturn('varchar');
        $attributeMock->expects($this->any())->method('getBackendType')->willReturn('decimal');

        $validateMock = $this->createMock(Validator::class);
        $this->validatorFactoryMock->expects($this->any())->method('create')->willReturn($validateMock);
        $validateMock->expects($this->any())->method('isValid')->willReturn(true);

        $this->model->save($attributeMock);
    }

    /**
     * Test save new attribute sets backend type, source model, backend model, and is_user_defined
     *
     * @return void
     */
    public function testSaveNewAttributeSetsBackendTypeSourceModelBackendModelAndIsUserDefined(): void
    {
        $attributeCode = 'new_attribute';
        $frontendInput = 'select';
        $backendType = 'int';
        $sourceModel = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
        $backendModel = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn('New Attribute');
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([]);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $attributeMock->expects($this->any())->method('getFrontendInput')->willReturn($frontendInput);
        $attributeMock->expects($this->once())->method('getBackendTypeByInput')
            ->with($frontendInput)
            ->willReturn($backendType);
        $attributeMock->expects($this->any())->method('getBackendType')->willReturn(null);
        $attributeMock->expects($this->once())->method('setBackendType')->with($backendType)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setSourceModel')->with($sourceModel)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setBackendModel')->with($backendModel)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setIsUserDefined')->with(1)->willReturnSelf();

        $this->productHelperMock->expects($this->once())
            ->method('getAttributeSourceModelByInputType')
            ->with($frontendInput)
            ->willReturn($sourceModel);
        $this->productHelperMock->expects($this->once())
            ->method('getAttributeBackendModelByInputType')
            ->with($frontendInput)
            ->willReturn($backendModel);

        $validatorMock = $this->createMock(Validator::class);
        $this->validatorFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->with($frontendInput)->willReturn(true);

        $this->attributeResourceMock->expects($this->once())->method('save')->with($attributeMock);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attributeMock);

        $this->model->save($attributeMock);
    }

    /**
     * Test save attribute with options sets option data correctly
     *
     * @return void
     */
    public function testSaveAttributeWithOptionsProcessesOptionsCorrectly(): void
    {
        $attributeCode = 'new_select_attribute';
        $frontendInput = 'select';
        $backendType = 'int';

        $storeLabelMock = $this->getMockForAbstractClass(AttributeOptionLabelInterface::class);
        $storeLabelMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $storeLabelMock->expects($this->once())->method('getLabel')->willReturn('Store Label');

        $option1Mock = $this->getMockForAbstractClass(AttributeOptionInterface::class);
        $option1Mock->expects($this->any())->method('getValue')->willReturn('option_value_1');
        $option1Mock->expects($this->once())->method('getLabel')->willReturn('Option 1');
        $option1Mock->expects($this->once())->method('getSortOrder')->willReturn(10);
        $option1Mock->expects($this->exactly(2))->method('getStoreLabels')->willReturn([$storeLabelMock]);
        $option1Mock->expects($this->once())->method('getIsDefault')->willReturn(true);

        $option2Mock = $this->getMockForAbstractClass(AttributeOptionInterface::class);
        $option2Mock->expects($this->any())->method('getValue')->willReturn(null);
        $option2Mock->expects($this->once())->method('getLabel')->willReturn('Option 2');
        $option2Mock->expects($this->once())->method('getSortOrder')->willReturn(null);
        $option2Mock->expects($this->once())->method('getStoreLabels')->willReturn(null);
        $option2Mock->expects($this->once())->method('getIsDefault')->willReturn(false);

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['setDefault', 'setOption'])
            ->onlyMethods([
                'getAttributeId',
                'setAttributeId',
                'getDefaultFrontendLabel',
                'getFrontendLabels',
                'getAttributeCode',
                'getFrontendInput',
                'getBackendTypeByInput',
                'getBackendType',
                'setBackendType',
                'setSourceModel',
                'setBackendModel',
                'setIsUserDefined',
                'getData',
                'getOptions',
                'setEntityTypeId'
            ])
            ->getMock();
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn('Select Attribute');
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([]);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $attributeMock->expects($this->any())->method('getFrontendInput')->willReturn($frontendInput);
        $attributeMock->expects($this->once())->method('getBackendTypeByInput')
            ->with($frontendInput)
            ->willReturn($backendType);
        $attributeMock->expects($this->any())->method('getBackendType')->willReturn(null);
        $attributeMock->expects($this->once())->method('setBackendType')->with($backendType)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setSourceModel')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setBackendModel')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setIsUserDefined')->with(1)->willReturnSelf();

        // Options data
        $attributeMock->expects($this->once())
            ->method('getData')
            ->with(ProductAttributeInterface::OPTIONS)
            ->willReturn([$option1Mock, $option2Mock]);
        $attributeMock->expects($this->once())
            ->method('getOptions')
            ->willReturn([$option1Mock, $option2Mock]);

        $expectedOptions = [
            'value' => [
                'option_value_1' => [0 => 'Option 1', 1 => 'Store Label'],
                'option_2' => [0 => 'Option 2']
            ],
            'order' => [
                'option_value_1' => 10,
                'option_2' => 0
            ]
        ];
        $attributeMock->expects($this->once())->method('setDefault')->with(['option_value_1']);
        $attributeMock->expects($this->once())->method('setOption')->with($expectedOptions);

        $validatorMock = $this->createMock(Validator::class);
        $this->validatorFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->with($frontendInput)->willReturn(true);

        $this->attributeResourceMock->expects($this->once())->method('save')->with($attributeMock);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attributeMock);

        $this->model->save($attributeMock);
    }

    /**
     * Test save throws exception for invalid attribute code
     *
     * @return void
     */
    public function testSaveThrowsExceptionForInvalidAttributeCode(): void
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Invalid value of "123_invalid_code" provided for the attribute_code field.');
        $this->attributeCodeValidatorMock
            ->expects($this->once())
            ->method('isValid')
            ->with('123_invalid_code')
            ->willThrowException(new \Magento\Framework\Exception\InputException(
                __('Invalid value of "%1" provided for the %2 field.', '123_invalid_code', 'attribute_code')
            ));
        // Add missing validator factory setup
        $validatorMock = $this->createMock(Validator::class);
        $validatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->validatorFactoryMock->expects($this->any())->method('create')->willReturn($validatorMock);

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn('Test Attribute');
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([]);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('123_invalid_code');
        // Expect setAttributeCode to be called with the existing code
        $attributeMock->expects($this->once())->method('setAttributeCode')->with('123_invalid_code')->willReturnSelf();
        // Add frontend input setup to ensure validation passes
        $attributeMock->expects($this->any())->method('getFrontendInput')->willReturn('text');
        // Add filterable method setup to prevent early validation failures
        $attributeMock->expects($this->any())->method('getIsFilterable')->willReturn(false);
        $attributeMock->expects($this->any())->method('getIsFilterableInSearch')->willReturn(false);
        $this->model->save($attributeMock);
    }

    /**
     * Test save throws exception for invalid frontend input
     *
     * @return void
     */
    public function testSaveThrowsExceptionForInvalidFrontendInput(): void
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Invalid value of "invalid_input" provided for the frontend_input field.');

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn('Test Attribute');
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([]);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('valid_code');
        $attributeMock->expects($this->any())->method('getFrontendInput')->willReturn('invalid_input');

        $validatorMock = $this->createMock(Validator::class);
        $this->validatorFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->with('invalid_input')->willReturn(false);

        $this->model->save($attributeMock);
    }

    /**
     * Test that generateCode creates valid attribute code from label
     *
     * @return void
     */
    public function testSaveGeneratesAttributeCodeFromLabel(): void
    {
        $frontendLabel = 'Test Attribute';
        $generatedCode = 'test_attribute';
        $frontendInput = 'text';
        $backendType = 'varchar';

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn($frontendLabel);
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([]);
        // Return null first to trigger generateCode, then return the generated code for subsequent calls
        $attributeMock->expects($this->any())->method('getAttributeCode')
            ->willReturnOnConsecutiveCalls(null, $generatedCode, $generatedCode);
        $attributeMock->expects($this->once())->method('setAttributeCode')->with($generatedCode)->willReturnSelf();
        $attributeMock->expects($this->any())->method('getFrontendInput')->willReturn($frontendInput);
        $attributeMock->expects($this->once())->method('getBackendTypeByInput')->willReturn($backendType);
        $attributeMock->expects($this->any())->method('getBackendType')->willReturn(null);
        $attributeMock->expects($this->once())->method('setBackendType')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setSourceModel')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setBackendModel')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setIsUserDefined')->willReturnSelf();

        $this->filterManagerMock->expects($this->once())
            ->method('translitUrl')
            ->with($frontendLabel)
            ->willReturn('test_attribute');

        $validatorMock = $this->createMock(Validator::class);
        $this->validatorFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->with($frontendInput)->willReturn(true);

        $this->attributeResourceMock->expects($this->once())->method('save')->with($attributeMock);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $generatedCode)
            ->willReturn($attributeMock);

        $this->model->save($attributeMock);
    }

    /**
     * Test that generateCode adds 'attr_' prefix when generated code is invalid
     *
     * @return void
     */
    public function testSaveGeneratesAttributeCodeWithPrefixWhenCodeInvalid(): void
    {
        $frontendLabel = '123 Numbers First';
        $transliteratedCode = '123_numbers_first';
        $expectedCode = 'attr_123_numbers_first';
        $frontendInput = 'text';
        $backendType = 'varchar';

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn($frontendLabel);
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([]);
        $attributeMock->expects($this->any())->method('getAttributeCode')
            ->willReturnOnConsecutiveCalls(null, $expectedCode, $expectedCode);
        $attributeMock->expects($this->once())->method('setAttributeCode')->with($expectedCode)->willReturnSelf();
        $attributeMock->expects($this->any())->method('getFrontendInput')->willReturn($frontendInput);
        $attributeMock->expects($this->once())->method('getBackendTypeByInput')->willReturn($backendType);
        $attributeMock->expects($this->any())->method('getBackendType')->willReturn(null);
        $attributeMock->expects($this->once())->method('setBackendType')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setSourceModel')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setBackendModel')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setIsUserDefined')->willReturnSelf();

        $this->filterManagerMock->expects($this->once())
            ->method('translitUrl')
            ->with($frontendLabel)
            ->willReturn($transliteratedCode);

        $validatorMock = $this->createMock(Validator::class);
        $this->validatorFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->with($frontendInput)->willReturn(true);

        $this->attributeResourceMock->expects($this->once())->method('save')->with($attributeMock);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $expectedCode)
            ->willReturn($attributeMock);

        $this->model->save($attributeMock);
    }

    /**
     * Test that generateCode generates hash-based code when label produces empty code
     *
     * @return void
     */
    public function testSaveGeneratesHashBasedCodeWhenLabelProducesEmptyCode(): void
    {
        $frontendLabel = '!!!';
        $frontendInput = 'text';
        $backendType = 'varchar';
        $generatedCode = null;

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn($frontendLabel);
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([]);
        // Use callback to capture the generated code and return it for subsequent calls
        $attributeMock->expects($this->any())->method('getAttributeCode')
            ->willReturnCallback(function () use (&$generatedCode) {
                return $generatedCode;
            });
        // Code will be 'attr_' + 8 char hash, capture it for later use
        $attributeMock->expects($this->once())
            ->method('setAttributeCode')
            ->with($this->matchesRegularExpression('/^attr_[a-f0-9]{8}$/'))
            ->willReturnCallback(function ($code) use (&$generatedCode, $attributeMock) {
                $generatedCode = $code;
                return $attributeMock;
            });
        $attributeMock->expects($this->any())->method('getFrontendInput')->willReturn($frontendInput);
        $attributeMock->expects($this->once())->method('getBackendTypeByInput')->willReturn($backendType);
        $attributeMock->expects($this->any())->method('getBackendType')->willReturn(null);
        $attributeMock->expects($this->once())->method('setBackendType')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setSourceModel')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setBackendModel')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setIsUserDefined')->willReturnSelf();

        // translitUrl returns empty string for special characters only
        $this->filterManagerMock->expects($this->once())
            ->method('translitUrl')
            ->with($frontendLabel)
            ->willReturn('');

        $validatorMock = $this->createMock(Validator::class);
        $this->validatorFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->with($frontendInput)->willReturn(true);

        $this->attributeResourceMock->expects($this->once())->method('save')->with($attributeMock);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $this->matchesRegularExpression('/^attr_[a-f0-9]{8}$/')
            )
            ->willReturn($attributeMock);

        $this->model->save($attributeMock);
    }
}
