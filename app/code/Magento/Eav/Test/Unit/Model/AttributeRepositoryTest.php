<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavResource;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollectionFactory;

    /**
     * @var AttributeSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeFactory;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $joinProcessor;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    private $model;

    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavResource = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute::class)
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

        $this->attributeFactory = $this->getMockBuilder(\Magento\Eav\Model\Entity\AttributeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->joinProcessor = $this->getMockBuilder(JoinProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->model = new \Magento\Eav\Model\AttributeRepository(
            $this->eavConfig,
            $this->eavResource,
            $this->attributeCollectionFactory,
            $this->searchResultsFactory,
            $this->attributeFactory,
            $this->joinProcessor,
            $this->collectionProcessor
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage entity_type_code is a required field.
     */
    public function testGetListInputException()
    {
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

        $entityTypeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
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
     * @param \PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock
     * @param \PHPUnit_Framework_MockObject_MockObject $attributeMock
     * @param int $collectionSize
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createSearchResultsMock($searchCriteriaMock, $attributeMock, $collectionSize)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $searchResultsMock */
        $searchResultsMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeSearchResultsInterface::class)
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createAttributeMock($attributeCode, $attributeId)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeInterface::class)
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
