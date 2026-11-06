<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Eav\Model\Attribute as WebsiteScopedAttribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes\ProviderInterface as DefaultAttributesProvider;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeRepositoryTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Config|MockObject
     */
    private $eavConfig;

    /**
     * @var Attribute|MockObject
     */
    private $eavResource;

    /**
     * @var CollectionFactory|MockObject
     */
    private $attributeCollectionFactory;

    /**
     * @var AttributeSearchResultsInterfaceFactory|MockObject
     */
    private $searchResultsFactory;

    /**
     * @var AttributeFactory|MockObject
     */
    private $attributeFactory;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    private $joinProcessor;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    /**
     * @var AttributeRepository
     */
    private $model;

    protected function setUp(): void
    {
        $this->eavConfig = $this->createPartialMock(
            Config::class,
            ['getEntityType', 'getAttribute', 'importAttributesData']
        );

        $this->eavResource = $this->createMock(Attribute::class);

        $this->attributeCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->searchResultsFactory = $this->createPartialMock(
            AttributeSearchResultsInterfaceFactory::class,
            ['create']
        );

        $this->attributeFactory = $this->createMock(AttributeFactory::class);

        $this->joinProcessor = $this->createMock(JoinProcessorInterface::class);

        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);

        $this->model = new AttributeRepository(
            $this->eavConfig,
            $this->eavResource,
            $this->attributeCollectionFactory,
            $this->searchResultsFactory,
            $this->attributeFactory,
            $this->joinProcessor,
            $this->collectionProcessor
        );
    }

    public function testGetListInputException()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('"entity_type_code" is required. Enter and try again.');
        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);

        $this->model->getList(null, $searchCriteriaMock);
    }

    /**
     * @return array<string, array{string, string|array, int, string[], int}>
     */
    public static function getListDataProvider(): array
    {
        return [
            'collection rows are imported into the EAV config' => [
                EntityAttribute::class,
                '*',
                1,
                ['sku'],
                0,
            ],
            'default entity attributes become static and global' => [
                EntityAttribute::class,
                '*',
                1,
                ['attribute_code'],
                1,
            ],
            'website-scoped attributes are loaded one by one' => [
                WebsiteScopedAttribute::class,
                [],
                0,
                ['attribute_code'],
                0,
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[DataProvider('getListDataProvider')]
    public function testGetList(
        string $attributeModel,
        string|array $additionalColumns,
        int $importCalls,
        array $defaultAttributes,
        int $staticOverrides
    ) {
        $entityTypeCode = 'entity_type_code';
        $eavEntityTypeTable = 'eav_entity_type_table';
        $eavEntityAttributeTable = 'eav_entity_attribute_table';
        $additionalTable = 'additional_table';
        $attributeCode = 'attribute_code';
        $attributeId = 1;
        $collectionSize = 1;

        $searchCriteriaMock = $this->createStub(SearchCriteriaInterface::class);
        $searchCriteriaMock->method('getPageSize')->willReturn($collectionSize);

        $entityTypeId = 4;
        $attributeData = [
            'attribute_id' => (string)$attributeId,
            'entity_type_id' => (string)$entityTypeId,
            'attribute_code' => $attributeCode,
            'attribute_model' => null,
            'backend_model' => null,
            'backend_type' => 'varchar',
            'frontend_input' => 'text',
            'frontend_label' => 'Attribute Label',
            'source_model' => null,
            'is_required' => '0',
            'is_user_defined' => '1',
            'is_global' => '0',
            'is_filterable' => '1',
            'is_searchable' => '1',
        ];
        $collectionItem = $this->createPartialMock(EntityAttribute::class, ['getData', 'getAttributeCode']);
        $collectionItem->method('getData')->willReturn(
            $attributeData + ['extension_attributes' => $this->createStub(ExtensionAttributesInterface::class)]
        );
        $collectionItem->method('getAttributeCode')->willReturn($attributeCode);

        $attributeCollectionMock = $this->createPartialMock(
            Collection::class,
            [
                'addFieldToFilter',
                'getTable',
                'join',
                'joinLeft',
                'addAttributeGrouping',
                'getIterator',
                'getSize'
            ]
        );
        $attributeCollectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('entity_type_code', ['eq' => $entityTypeCode])
            ->willReturnSelf();
        $attributeCollectionMock->expects($this->exactly(3))
            ->method('getTable')
            ->willReturnMap([
                ['eav_entity_type', $eavEntityTypeTable],
                ['eav_entity_attribute', $eavEntityAttributeTable],
                [$additionalTable, $additionalTable],
            ]);
        $joins = [];
        $attributeCollectionMock->expects($this->exactly(2))
            ->method('join')
            ->willReturnCallback(
                function ($table, $condition, $columns) use (&$joins, $attributeCollectionMock) {
                    $joins[] = [$table, $condition, $columns];
                    return $attributeCollectionMock;
                }
            );
        $attributeCollectionMock->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['eav_entity_attribute' => $eavEntityAttributeTable],
                'main_table.attribute_id = eav_entity_attribute.attribute_id',
                []
            )
            ->willReturnSelf();
        $attributeCollectionMock->expects($this->once())
            ->method('addAttributeGrouping')
            ->willReturnSelf();
        $attributeCollectionMock->expects($this->exactly(1 + $importCalls))
            ->method('getIterator')
            ->willReturnCallback(fn () => new \ArrayIterator([$collectionItem]));
        $attributeCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);

        $this->attributeCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($attributeCollectionMock);

        $this->joinProcessor->expects($this->once())
            ->method('process')
            ->with($attributeCollectionMock)
            ->willReturnSelf();

        $entityTypeMock = $this->createPartialMockWithReflection(
            Type::class,
            ['getAdditionalAttributeTable', 'getAttributeModel', 'getEntity', 'getId']
        );
        $entity = $this->createStub(DefaultAttributesProvider::class);
        $entity->method('getDefaultAttributes')->willReturn($defaultAttributes);
        $entityTypeMock->expects($this->exactly($importCalls))->method('getEntity')->willReturn($entity);
        $entityTypeMock->method('getId')->willReturn($entityTypeId);
        $attributeMock = $this->createPartialMock(EntityAttribute::class, ['getResource']);
        $attributeMock->setData(
            ['attribute_id' => $attributeId, 'attribute_code' => $attributeCode, 'backend_type' => 'varchar']
        );
        $entityTypeMock->expects($this->once())
            ->method('getAdditionalAttributeTable')
            ->willReturn($additionalTable);
        $entityTypeMock->method('getAttributeModel')->willReturn($attributeModel);
        $this->eavConfig->expects($this->exactly($importCalls))
            ->method('importAttributesData')
            ->with($entityTypeMock, [$attributeData])
            ->willReturnSelf();

        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with($entityTypeCode)
            ->willReturn($entityTypeMock);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with($entityTypeCode, $attributeCode)
            ->willReturn($attributeMock);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $attributeCollectionMock)
            ->willReturnSelf();

        $searchResultsMock = $this->createSearchResultsMock($searchCriteriaMock, $attributeMock, $collectionSize);

        $this->searchResultsFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $this->assertEquals($searchResultsMock, $this->model->getList($entityTypeCode, $searchCriteriaMock));
        $this->assertSame($importCalls ? $entityTypeMock : null, $attributeMock->getData('entity_type'));
        $this->assertSame($importCalls ? $entityTypeId : null, $attributeMock->getData('entity_type_id'));
        $this->assertSame($staticOverrides ? 'static' : 'varchar', $attributeMock->getBackendType());
        $this->assertSame($staticOverrides ? 1 : null, $attributeMock->getData('is_global'));
        $this->assertSame(
            [
                [
                    ['entity_type' => $eavEntityTypeTable],
                    'main_table.entity_type_id = entity_type.entity_type_id',
                    []
                ],
                [
                    ['additional_table' => $additionalTable],
                    'main_table.attribute_id = additional_table.attribute_id',
                    $additionalColumns
                ],
            ],
            $joins
        );
    }

    /**
     * @param MockObject $searchCriteriaMock
     * @param MockObject $attributeMock
     * @param int $collectionSize
     * @return MockObject
     */
    protected function createSearchResultsMock($searchCriteriaMock, $attributeMock, $collectionSize)
    {
        /** @var MockObject $searchResultsMock */
        $searchResultsMock = $this->createMock(AttributeSearchResultsInterface::class);

        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$attributeMock])
            ->willReturnSelf();
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize)
            ->willReturnSelf();

        return $searchResultsMock;
    }
}
