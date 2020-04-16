<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model;

/**
 * Class CustomAttributesMapperTest
 */
class CustomAttributesMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testEntityToDatabase()
    {
        $searchResult = $this->getMockBuilder(\Magento\Framework\Api\SearchResults::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $searchResult->expects($this->any())
            ->method('getItems')
            ->willReturn($this->getAttributes());

        $attributeRepository = $this->getMockBuilder(\Magento\Eav\Model\AttributeRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();
        $attributeRepository->expects($this->any())
            ->method('getList')
            ->willReturn($searchResult);

        $metadata = $this->objectManager->getObject(
            \Magento\Framework\EntityManager\EntityMetadata::class,
            [
                'entityTableName' => 'test',
                'identifierField' => 'entity_id',
                'eavEntityType' => 'customer_address'
            ]
        );

        $metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMetadata', 'hasConfiguration'])
            ->getMock();
        $metadataPool->expects($this->any())
            ->method('hasConfiguration')
            ->willReturn(true);
        $metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with($this->equalTo(\Magento\Framework\Api\CustomAttributesDataInterface::class))
            ->willReturn($metadata);
        $metadataPool->expects($this->once())
            ->method('hasConfiguration')
            ->willReturn(true);

        $searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter', 'create'])
            ->getMock();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilder->expects($this->any())
            ->method('addFilter')
            ->willReturn($searchCriteriaBuilder);
        $searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteria);

        /** @var \Magento\Eav\Model\CustomAttributesMapper $customAttributesMapper */
        $customAttributesMapper = $this->objectManager
            ->getObject(\Magento\Eav\Model\CustomAttributesMapper::class, [
                'attributeRepository' => $attributeRepository,
                'metadataPool' => $metadataPool,
                'searchCriteriaBuilder' => $searchCriteriaBuilder
            ]);

        $actual = $customAttributesMapper->entityToDatabase(
            \Magento\Framework\Api\CustomAttributesDataInterface::class,
            [
                \Magento\Framework\Api\CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                    'test' => [
                        \Magento\Framework\Api\AttributeInterface::ATTRIBUTE_CODE => 'test',
                        \Magento\Framework\Api\AttributeInterface::VALUE => 'test'
                    ],
                    'test1' => [
                        \Magento\Framework\Api\AttributeInterface::ATTRIBUTE_CODE => 'test4',
                        \Magento\Framework\Api\AttributeInterface::VALUE => 'test4'
                    ],
                    'test2' => [
                        \Magento\Framework\Api\AttributeInterface::ATTRIBUTE_CODE => 'test2',
                        \Magento\Framework\Api\AttributeInterface::VALUE => 'test2'
                    ]
                ]
            ]
        );
        $expected = [
            \Magento\Framework\Api\CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                'test1' => [
                    \Magento\Framework\Api\AttributeInterface::ATTRIBUTE_CODE => 'test4',
                    \Magento\Framework\Api\AttributeInterface::VALUE => 'test4'
                ],
                'test2' => [
                    \Magento\Framework\Api\AttributeInterface::ATTRIBUTE_CODE => 'test2',
                    \Magento\Framework\Api\AttributeInterface::VALUE => 'test2'
                ],
            ],
            'test' => 'test'
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testDatabaseToEntity()
    {
        $searchResult = $this->getMockBuilder(\Magento\Framework\Api\SearchResults::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $searchResult->expects($this->any())
            ->method('getItems')
            ->willReturn($this->getAttributes());

        $attributeRepository = $this->getMockBuilder(\Magento\Eav\Model\AttributeRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();
        $attributeRepository->expects($this->any())
            ->method('getList')
            ->willReturn($searchResult);

        $metadata = $this->objectManager->getObject(
            \Magento\Framework\EntityManager\EntityMetadata::class,
            [
                'entityTableName' => 'test',
                'identifierField' => 'entity_id',
                'eavEntityType' => 'customer_address'
            ]
        );

        $metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMetadata'])
            ->getMock();
        $metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with($this->equalTo(\Magento\Framework\Api\CustomAttributesDataInterface::class))
            ->willReturn($metadata);

        $searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter', 'create'])
            ->getMock();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilder->expects($this->any())
            ->method('addFilter')
            ->willReturn($searchCriteriaBuilder);
        $searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteria);

        /** @var \Magento\Eav\Model\CustomAttributesMapper $customAttributesMapper */
        $customAttributesMapper = $this->objectManager
            ->getObject(\Magento\Eav\Model\CustomAttributesMapper::class, [
                'attributeRepository' => $attributeRepository,
                'metadataPool' => $metadataPool,
                'searchCriteriaBuilder' => $searchCriteriaBuilder
            ]);
        $actual = $customAttributesMapper->databaseToEntity(
            \Magento\Framework\Api\CustomAttributesDataInterface::class,
            [
                'test' => 'test',
                'test4' => 'test4',
                'test2' => 'test2'
            ]
        );
        $expected = [
            'test4' => 'test4',
            'test2' => 'test2',
            \Magento\Framework\Api\CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                [
                    \Magento\Framework\Api\AttributeInterface::ATTRIBUTE_CODE => 'test',
                    \Magento\Framework\Api\AttributeInterface::VALUE => 'test'
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
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isStatic', 'getAttributeCode'])
            ->getMockForAbstractClass();
        $attribute->expects($this->any())
            ->method('isStatic')
            ->willReturn(false);
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('test');

        /* Attribute with the code we don't want to copy */
        $attribute1 = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isStatic', 'getAttributeCode'])
            ->getMockForAbstractClass();
        $attribute1->expects($this->any())
            ->method('isStatic')
            ->willReturn(false);
        $attribute1->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('test1');

        /* Static attribute but with the code which exists in custom attributes */
        $attribute2 = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isStatic', 'getAttributeCode'])
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
