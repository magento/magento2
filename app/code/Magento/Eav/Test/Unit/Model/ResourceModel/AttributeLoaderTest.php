<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\ResourceModel;

class AttributeLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Eav\Model\ResourceModel\AttributeLoader
     */
    private $attributeLoader;

    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(\Magento\Eav\Api\AttributeRepositoryInterface::class);
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->attributeLoader = $objectManagerHelper->getObject(
            \Magento\Eav\Model\ResourceModel\AttributeLoader::class,
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
        $metadataMock = $this->createMock(\Magento\Framework\EntityManager\EntityMetadataInterface::class);
        $metadataMock->expects($this->once())
            ->method('getEavEntityType')
            ->willReturn($entityType);
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($metadataMock);

        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(
                \Magento\Eav\Model\ResourceModel\AttributeLoader::ATTRIBUTE_SET_ID,
                $attributeSetId,
                $expectedCondition
            )->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        $attributeMock = $this->createMock(\Magento\Eav\Api\Data\AttributeInterface::class);
        $searchResultMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSearchResultsInterface::class);
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
