<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeRepositoryTest extends TestCase
{
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
        $this->eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavResource = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->searchResultsFactory = $this->getMockBuilder(AttributeSearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->attributeFactory = $this->getMockBuilder(AttributeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->joinProcessor = $this->getMockBuilder(JoinProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

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
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMockForAbstractClass();

        $this->model->getList(null, $searchCriteriaMock);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $entityTypeCode = 'entity_type_code';
        $eavEntityTypeTable = 'eav_entity_type_table';
        $eavEntityAttributeTable = 'eav_entity_attribute_table';
        $additionalTable = 'additional_table';
        $attributeCode = 'attribute_code';
        $attributeId = 1;
        $collectionSize = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->setMethods(['getPageSize'])
            ->getMockForAbstractClass();

        $searchCriteriaMock->expects($this->any())
            ->method('getPageSize')
            ->willReturn($collectionSize);

        $attributeMock = $this->createAttributeMock($attributeCode, $attributeId);

        $attributeCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $attributeCollectionMock->expects($this->exactly(2))
            ->method('join')
            ->willReturnMap([
                [
                    ['entity_type' => $eavEntityTypeTable],
                    'main_table.entity_type_id = entity_type.entity_type_id',
                    []
                ],
                [
                    ['additional_table' => $additionalTable],
                    'main_table.attribute_id = additional_table.attribute_id',
                    []
                ]
            ]);
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
        $attributeCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$attributeMock]));
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

        $entityTypeMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdditionalAttributeTable'])
            ->getMock();
        $entityTypeMock->expects($this->once())
            ->method('getAdditionalAttributeTable')
            ->willReturn($additionalTable);

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
        $searchResultsMock = $this->getMockBuilder(AttributeSearchResultsInterface::class)
            ->getMockForAbstractClass();

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

    /**
     * @param string $attributeCode
     * @param int $attributeId
     * @return MockObject
     */
    protected function createAttributeMock($attributeCode, $attributeId)
    {
        /** @var MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AttributeInterface::class)
            ->setMethods([
                'getAttributeCode',
                'getAttributeId',
            ])
            ->getMockForAbstractClass();

        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attributeMock->expects($this->once())
            ->method('getAttributeId')
            ->willReturn($attributeId);

        return $attributeMock;
    }
}
