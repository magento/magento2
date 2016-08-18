<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Test\Unit;

use Magento\Framework\App\ResourceConnection;

class CustomAttributesMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    public function setUp()
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
            ->will($this->returnValue($this->getAttributes()));

        $attributeRepository = $this->getMockBuilder(\Magento\Eav\Model\AttributeRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();
        $attributeRepository->expects($this->any())
            ->method('getList')
            ->will($this->returnValue($searchResult));

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
            ->with($this->equalTo(\Magento\Customer\Api\Data\AddressInterface::class))
            ->will($this->returnValue($metadata));
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
            ->will($this->returnValue($searchCriteriaBuilder));
        $searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->will($this->returnValue($searchCriteria));

        /** @var \Magento\Framework\EntityManager\CustomAttributesMapper $customAttributesMapper */
        $customAttributesMapper = $this->objectManager
            ->getObject(\Magento\Framework\EntityManager\CustomAttributesMapper::class, [
                'attributeRepository' => $attributeRepository,
                'metadataPool' => $metadataPool,
                'searchCriteriaBuilder' => $searchCriteriaBuilder
            ]);

        $actual = $customAttributesMapper->entityToDatabase(
            \Magento\Customer\Api\Data\AddressInterface::class,
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
            ->will($this->returnValue($this->getAttributes()));

        $attributeRepository = $this->getMockBuilder(\Magento\Eav\Model\AttributeRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();
        $attributeRepository->expects($this->any())
            ->method('getList')
            ->will($this->returnValue($searchResult));

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
            ->with($this->equalTo(\Magento\Customer\Api\Data\AddressInterface::class))
            ->will($this->returnValue($metadata));

        $searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter', 'create'])
            ->getMock();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilder->expects($this->any())
            ->method('addFilter')
            ->will($this->returnValue($searchCriteriaBuilder));
        $searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->will($this->returnValue($searchCriteria));

        /** @var \Magento\Framework\EntityManager\CustomAttributesMapper $customAttributesMapper */
        $customAttributesMapper = $this->objectManager
            ->getObject(\Magento\Framework\EntityManager\CustomAttributesMapper::class, [
                'attributeRepository' => $attributeRepository,
                'metadataPool' => $metadataPool,
                'searchCriteriaBuilder' => $searchCriteriaBuilder
            ]);
        $actual = $customAttributesMapper->databaseToEntity(
            \Magento\Customer\Api\Data\AddressInterface::class,
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

    private function getAttributes()
    {
        /* Attribute with the code we want to copy */
        $attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isStatic', 'getAttributeCode'])
            ->getMock();
        $attribute->expects($this->any())
            ->method('isStatic')
            ->will($this->returnValue(false));
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('test'));

        /* Attribute with the code we don't want to copy */
        $attribute1 = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isStatic', 'getAttributeCode'])
            ->getMock();
        $attribute1->expects($this->any())
            ->method('isStatic')
            ->will($this->returnValue(false));
        $attribute1->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('test1'));

        /* Static attribute but with the code which exists in custom attributes */
        $attribute2 = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isStatic', 'getAttributeCode'])
            ->getMock();
        $attribute2->expects($this->any())
            ->method('isStatic')
            ->will($this->returnValue(true));
        $attribute2->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('test2'));

        return [$attribute, $attribute1, $attribute2];
    }
}
