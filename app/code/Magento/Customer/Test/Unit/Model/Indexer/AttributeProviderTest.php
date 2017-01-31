<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Indexer;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Indexer\AttributeProvider;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;

class AttributeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var AttributeProvider
     */
    protected $object;

    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->object = new AttributeProvider(
            $this->eavConfig
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAddDynamicData()
    {
        $existentName = 'field';
        $existentField = [
            'name' => $existentName,
            'handler' => 'handler',
            'origin' => $existentName,
            'type' => 'type',
            'filters' => ['filter'],
            'dataType' => 'data_type',
        ];
        $data = ['fields' => [$existentName => $existentField]];
        $attrName = 'attrName';
        $attrBackendType = 'b_type';
        $attrFrontendInput = 'int';

        /** @var \Magento\Eav\Model\Entity\Type|\PHPUnit_Framework_MockObject_MockObject $collectionMock $entityType */
        $entityType = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject $entity */
        $entity = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject $attribute */
        $attribute = $this->getMockBuilder('Magento\Customer\Model\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setEntity',
                    'getName',
                    'getFrontendInput',
                    'getBackendType',
                    'canBeSearchableInGrid',
                    'canBeFilterableInGrid',
                    'getData',
                ]
            )
            ->getMock();
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Customer::ENTITY)
            ->willReturn($entityType);
        $entityType->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);
        $entityType->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$attribute]);
        $attribute->expects($this->once())
            ->method('setEntity')
            ->with($entity)
            ->willReturnSelf();
        $attribute->expects($this->exactly(3))
            ->method('getName')
            ->willReturn($attrName);
        $attribute->expects($this->any())
            ->method('getBackendType')
            ->willReturn($attrBackendType);
        $attribute->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($attrFrontendInput);
        $attribute->expects($this->any())
            ->method('canBeSearchableInGrid')
            ->willReturn(false);
        $attribute->expects($this->any())
            ->method('canBeFilterableInGrid')
            ->willReturn(false);
        $attribute->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['is_used_in_grid', null, true],
                ]
            );

        $this->assertEquals(
            ['fields' =>
                [
                    $existentName => $existentField,
                    $attrName => [
                        'name' => $attrName,
                        'handler' => 'Magento\Framework\Indexer\Handler\AttributeHandler',
                        'origin' => $attrName,
                        'type' => 'virtual',
                        'filters' => [],
                        'dataType' => $attrBackendType,
                        'entity' => Customer::ENTITY,
                        'bind' => null,
                    ],
                ],
            ],
            $this->object->addDynamicData($data)
        );
    }

    public function testAddDynamicDataWithStaticAndSearchable()
    {
        $existentName = 'field';
        $existentField = [
            'name' => $existentName,
            'handler' => 'handler',
            'origin' => $existentName,
            'type' => 'searchable',
            'filters' => ['filter'],
            'dataType' => 'data_type',
        ];
        $data = ['fields' => [$existentName => $existentField]];
        $attrName = $existentName;
        $attrBackendType = 'static';
        $attrFrontendInput = 'text';

        /** @var \Magento\Eav\Model\Entity\Type|\PHPUnit_Framework_MockObject_MockObject $collectionMock $entityType */
        $entityType = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject $entity */
        $entity = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject $attribute */
        $attribute = $this->getMockBuilder('Magento\Customer\Model\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setEntity',
                    'getName',
                    'getFrontendInput',
                    'getBackendType',
                    'canBeSearchableInGrid',
                    'canBeFilterableInGrid',
                    'getData',
                ]
            )
            ->getMock();
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Customer::ENTITY)
            ->willReturn($entityType);
        $entityType->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$attribute]);
        $entityType->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);
        $attribute->expects($this->once())
            ->method('setEntity')
            ->with($entity)
            ->willReturnSelf();
        $attribute->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($attrFrontendInput);
        $attribute->expects($this->any())
            ->method('getBackendType')
            ->willReturn($attrBackendType);
        $attribute->expects($this->any())
            ->method('canBeSearchableInGrid')
            ->willReturn(true);
        $attribute->expects($this->never())
            ->method('canBeFilterableInGrid');

        $this->assertEquals(
            ['fields' =>
                [
                    $attrName => [
                        'name' => $attrName,
                        'handler' => 'handler',
                        'origin' => $attrName,
                        'type' => 'searchable',
                        'filters' => ['filter'],
                        'dataType' => 'data_type',
                    ],
                ],
            ],
            $this->object->addDynamicData($data)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAddDynamicDataWithStaticAndFilterable()
    {
        $existentName = 'field';
        $existentField = [
            'name' => $existentName,
            'handler' => 'handler',
            'origin' => $existentName,
            'type' => 'type',
            'filters' => ['filter'],
            'dataType' => 'data_type',
        ];
        $data = [
            'fields' => [$existentName => $existentField],
            'references' => [
                'customer' => [
                    'to' => 'to_field',
                ],
            ],
        ];
        $attrName = $existentName;
        $attrBackendType = 'varchar';
        $attrFrontendInput = 'text';

        /** @var \Magento\Eav\Model\Entity\Type|\PHPUnit_Framework_MockObject_MockObject $collectionMock $entityType */
        $entityType = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject $entity */
        $entity = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject $attribute */
        $attribute = $this->getMockBuilder('Magento\Customer\Model\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setEntity',
                    'getName',
                    'getFrontendInput',
                    'getBackendType',
                    'canBeSearchableInGrid',
                    'canBeFilterableInGrid',
                    'getData',
                ]
            )
            ->getMock();
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Customer::ENTITY)
            ->willReturn($entityType);
        $entityType->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$attribute]);
        $entityType->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);
        $attribute->expects($this->once())
            ->method('setEntity')
            ->with($entity)
            ->willReturnSelf();
        $attribute->expects($this->exactly(3))
            ->method('getName')
            ->willReturn($attrName);
        $attribute->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($attrFrontendInput);
        $attribute->expects($this->any())
            ->method('getBackendType')
            ->willReturn($attrBackendType);
        $attribute->expects($this->any())
            ->method('canBeSearchableInGrid')
            ->willReturn(false);
        $attribute->expects($this->any())
            ->method('canBeFilterableInGrid')
            ->willReturn(true);
        $attribute->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['is_used_in_grid', null, true],
                ]
            );

        $this->assertEquals(
            ['fields' =>
                [
                    $attrName => [
                        'name' => $attrName,
                        'handler' => 'Magento\Framework\Indexer\Handler\AttributeHandler',
                        'origin' => $attrName,
                        'type' => 'filterable',
                        'filters' => [],
                        'dataType' => 'varchar',
                        'entity' => Customer::ENTITY,
                        'bind' => 'to_field',
                    ],
                ],
                'references' => [
                    'customer' => [
                        'to' => 'to_field',
                    ],
                ],
            ],
            $this->object->addDynamicData($data)
        );
    }
}
