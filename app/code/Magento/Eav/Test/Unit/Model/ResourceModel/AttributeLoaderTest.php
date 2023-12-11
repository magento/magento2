<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\ResourceModel;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Eav\Model\ResourceModel\AttributeLoader;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeLoaderTest extends TestCase
{
    /**
     * @var AttributeRepositoryInterface|MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var AttributeLoader
     */
    private $attributeLoader;

    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->getMockForAbstractClass(AttributeRepositoryInterface::class);
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);
        $this->attributeLoader = $objectManagerHelper->getObject(
            AttributeLoader::class,
            [
                'attributeRepository' => $this->attributeRepositoryMock,
                'metadataPool' => $this->metadataPoolMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * @param string $entityType
     * @param int|null $attributeSetId
     * @param string $expectedCondition
     * @dataProvider getAttributesDataProvider
     */
    public function testGetAttributes($entityType, $attributeSetId, $expectedCondition)
    {
        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $metadataMock->expects($this->once())
            ->method('getEavEntityType')
            ->willReturn($entityType);
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($metadataMock);

        $searchCriteria = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(
                AttributeLoader::ATTRIBUTE_SET_ID,
                $attributeSetId,
                $expectedCondition
            )->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        $attributeMock = $this->getMockForAbstractClass(AttributeInterface::class);
        $searchResultMock = $this->getMockForAbstractClass(AttributeSearchResultsInterface::class);
        $searchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$attributeMock]);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($entityType, $searchCriteria)
            ->willReturn($searchResultMock);

        $this->assertEquals([$attributeMock], $this->attributeLoader->getAttributes($entityType, $attributeSetId));
    }

    /**
     * @return array
     */
    public function getAttributesDataProvider()
    {
        return [
            ['entity-type', null, 'neq'],
            ['entity-type', 1, 'eq']
        ];
    }
}
