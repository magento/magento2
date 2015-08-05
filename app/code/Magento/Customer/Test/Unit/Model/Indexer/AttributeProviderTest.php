<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Indexer;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Indexer\AttributeProvider;

class AttributeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Customer\Model\Resource\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var AttributeProvider
     */
    protected $object;

    public function setUp()
    {
        $this->eavConfig = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder('Magento\Customer\Model\Resource\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->object = new AttributeProvider(
            $this->eavConfig,
            $this->collection
        );
    }

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

        $entityType = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $entity = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setEntity',
                    'getName',
                    'getFrontendInput',
                    'getBackendTypeByInput',
                    'getData',
                ]
            )
            ->getMock();
        $this->collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$attribute]);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Customer::ENTITY)
            ->willReturn($entityType);
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
            ->willReturn('frontendInput');
        $attribute->expects($this->any())
            ->method('getBackendTypeByInput')
            ->with('frontendInput')
            ->willReturn($attrBackendType);
        $attribute->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['is_used_in_grid', null, true],
                    ['is_searchable_in_grid', null, false],
                    ['is_filterable_in_grid', null, false],
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

        $entityType = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $entity = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setEntity',
                    'getName',
                    'getFrontendInput',
                    'getBackendTypeByInput',
                    'getData',
                ]
            )
            ->getMock();
        $this->collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$attribute]);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Customer::ENTITY)
            ->willReturn($entityType);
        $entityType->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);
        $attribute->expects($this->once())
            ->method('setEntity')
            ->with($entity)
            ->willReturnSelf();
        $attribute->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('frontendInput');
        $attribute->expects($this->any())
            ->method('getBackendTypeByInput')
            ->with('frontendInput')
            ->willReturn($attrBackendType);
        $attribute->expects($this->once())
            ->method('getData')
            ->willReturnMap(
                [
                    ['is_searchable_in_grid', null, true],
                ]
            );

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

        $entityType = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $entity = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setEntity',
                    'getName',
                    'getFrontendInput',
                    'getBackendTypeByInput',
                    'getData',
                ]
            )
            ->getMock();
        $this->collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$attribute]);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Customer::ENTITY)
            ->willReturn($entityType);
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
            ->willReturn('frontendInput');
        $attribute->expects($this->any())
            ->method('getBackendTypeByInput')
            ->with('frontendInput')
            ->willReturn($attrBackendType);
        $attribute->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['is_used_in_grid', null, true],
                    ['is_searchable_in_grid', null, false],
                    ['is_filterable_in_grid', null, true],
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
