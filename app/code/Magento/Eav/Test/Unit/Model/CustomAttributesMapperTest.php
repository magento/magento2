<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\CustomAttributesMapper;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CustomAttributesMapperTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testEntityToDatabase()
    {
        $searchResult = $this->getMockBuilder(SearchResults::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems'])
            ->getMock();
        $searchResult->expects($this->any())
            ->method('getItems')
            ->willReturn($this->getAttributes());

        $attributeRepository = $this->getMockBuilder(AttributeRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getList'])
            ->getMock();
        $attributeRepository->expects($this->any())
            ->method('getList')
            ->willReturn($searchResult);

        $metadata = $this->objectManager->getObject(
            EntityMetadata::class,
            [
                'entityTableName' => 'test',
                'identifierField' => 'entity_id',
                'eavEntityType' => 'customer_address'
            ]
        );

        $metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMetadata', 'hasConfiguration'])
            ->getMock();
        $metadataPool->expects($this->any())
            ->method('hasConfiguration')
            ->willReturn(true);
        $metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with(CustomAttributesDataInterface::class)
            ->willReturn($metadata);
        $metadataPool->expects($this->once())
            ->method('hasConfiguration')
            ->willReturn(true);

        $searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFilter', 'create'])
            ->getMock();
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilder->expects($this->any())
            ->method('addFilter')
            ->willReturn($searchCriteriaBuilder);
        $searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteria);

        /** @var CustomAttributesMapper $customAttributesMapper */
        $customAttributesMapper = $this->objectManager
            ->getObject(CustomAttributesMapper::class, [
                'attributeRepository' => $attributeRepository,
                'metadataPool' => $metadataPool,
                'searchCriteriaBuilder' => $searchCriteriaBuilder
            ]);

        $actual = $customAttributesMapper->entityToDatabase(
            CustomAttributesDataInterface::class,
            [
                CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                    'test' => [
                        AttributeInterface::ATTRIBUTE_CODE => 'test',
                        AttributeInterface::VALUE => 'test'
                    ],
                    'test1' => [
                        AttributeInterface::ATTRIBUTE_CODE => 'test4',
                        AttributeInterface::VALUE => 'test4'
                    ],
                    'test2' => [
                        AttributeInterface::ATTRIBUTE_CODE => 'test2',
                        AttributeInterface::VALUE => 'test2'
                    ]
                ]
            ]
        );
        $expected = [
            CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                'test1' => [
                    AttributeInterface::ATTRIBUTE_CODE => 'test4',
                    AttributeInterface::VALUE => 'test4'
                ],
                'test2' => [
                    AttributeInterface::ATTRIBUTE_CODE => 'test2',
                    AttributeInterface::VALUE => 'test2'
                ],
            ],
            'test' => 'test'
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testDatabaseToEntity()
    {
        $searchResult = $this->getMockBuilder(SearchResults::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems'])
            ->getMock();
        $searchResult->expects($this->any())
            ->method('getItems')
            ->willReturn($this->getAttributes());

        $attributeRepository = $this->getMockBuilder(AttributeRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getList'])
            ->getMock();
        $attributeRepository->expects($this->any())
            ->method('getList')
            ->willReturn($searchResult);

        $metadata = $this->objectManager->getObject(
            EntityMetadata::class,
            [
                'entityTableName' => 'test',
                'identifierField' => 'entity_id',
                'eavEntityType' => 'customer_address'
            ]
        );

        $metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMetadata'])
            ->getMock();
        $metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with(CustomAttributesDataInterface::class)
            ->willReturn($metadata);

        $searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFilter', 'create'])
            ->getMock();
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilder->expects($this->any())
            ->method('addFilter')
            ->willReturn($searchCriteriaBuilder);
        $searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteria);

        /** @var CustomAttributesMapper $customAttributesMapper */
        $customAttributesMapper = $this->objectManager
            ->getObject(CustomAttributesMapper::class, [
                'attributeRepository' => $attributeRepository,
                'metadataPool' => $metadataPool,
                'searchCriteriaBuilder' => $searchCriteriaBuilder
            ]);
        $actual = $customAttributesMapper->databaseToEntity(
            CustomAttributesDataInterface::class,
            [
                'test' => 'test',
                'test4' => 'test4',
                'test2' => 'test2'
            ]
        );
        $expected = [
            'test4' => 'test4',
            'test2' => 'test2',
            CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                [
                    AttributeInterface::ATTRIBUTE_CODE => 'test',
                    AttributeInterface::VALUE => 'test'
                ]
            ],
            'test' => 'test'
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    private function getAttributes()
    {
        /* Attribute with the code we want to copy */
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isStatic', 'getAttributeCode'])
            ->getMockForAbstractClass();
        $attribute->expects($this->any())
            ->method('isStatic')
            ->willReturn(false);
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('test');

        /* Attribute with the code we don't want to copy */
        $attribute1 = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isStatic', 'getAttributeCode'])
            ->getMockForAbstractClass();
        $attribute1->expects($this->any())
            ->method('isStatic')
            ->willReturn(false);
        $attribute1->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('test1');

        /* Static attribute but with the code which exists in custom attributes */
        $attribute2 = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isStatic', 'getAttributeCode'])
            ->getMockForAbstractClass();
        $attribute2->expects($this->any())
            ->method('isStatic')
            ->willReturn(true);
        $attribute2->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('test2');

        return [$attribute, $attribute1, $attribute2];
    }
}
