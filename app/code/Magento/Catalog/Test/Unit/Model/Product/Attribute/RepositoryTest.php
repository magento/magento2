<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterface;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeResourceMock =
            $this->createMock(\Magento\Catalog\Model\ResourceModel\Attribute::class);
        $this->productHelperMock =
            $this->createMock(Product::class);
        $this->filterManagerMock =
            $this->createMock(FilterManager::class);
        $this->eavAttributeRepositoryMock =
            $this->getMockForAbstractClass(AttributeRepositoryInterface::class);
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->eavConfigMock->expects($this->any())->method('getEntityType')
            ->willReturn(new DataObject(['default_attribute_set_id' => 4]));
        $this->validatorFactoryMock = $this->createPartialMock(
            ValidatorFactory::class,
            ['create']
        );
        $this->searchCriteriaBuilderMock =
            $this->createMock(SearchCriteriaBuilder::class);
        $this->searchResultMock =
            $this->getMockBuilder(SearchResultsInterface::class)
                ->onlyMethods(
                    ['getItems', 'getSearchCriteria', 'getTotalCount', 'setItems', 'setSearchCriteria', 'setTotalCount']
                )
                ->getMockForAbstractClass();

        $this->model = new Repository(
            $this->attributeResourceMock,
            $this->productHelperMock,
            $this->filterManagerMock,
            $this->eavAttributeRepositoryMock,
            $this->eavConfigMock,
            $this->validatorFactoryMock,
            $this->searchCriteriaBuilderMock
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
        $itemMock = $this->getMockForAbstractClass(ProductInterface::class);
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
     * @dataProvider filterableDataProvider
     */
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
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([$labelMock]);
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn(null);
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
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn($attributeId);
        $attributeMock->expects($this->once())->method('setBackendModel')->with($backendModel)->willReturnSelf();

        $existingModelMock = $this->createMock(Attribute::class);
        $existingModelMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $existingModelMock->expects($this->any())->method('getAttributeId')->willReturn($attributeId);
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
        $labelMock = $this->getMockForAbstractClass(AttributeFrontendLabelInterface::class);
        $labelMock->expects($this->any())->method('getStoreId')->willReturn(1);
        $labelMock->expects($this->any())->method('getLabel')->willReturn('Store Scope Label');

        $attributeId = 1;
        $attributeCode = 'existing_attribute_code';
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn($attributeId);
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn(null);
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([$labelMock]);
        $attributeMock->expects($this->any())->method('getOptions')->willReturn([]);
        $attributeMock->expects($this->once())->method('setBackendModel')->with($backendModel)->willReturnSelf();

        $existingModelMock = $this->createMock(Attribute::class);
        $existingModelMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn('Default Label');
        $existingModelMock->expects($this->any())->method('getAttributeId')->willReturn($attributeId);
        $existingModelMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
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
}
