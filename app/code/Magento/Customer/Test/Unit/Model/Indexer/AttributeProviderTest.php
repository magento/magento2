<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Indexer;

use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Indexer\AttributeProvider;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Indexer\Handler\AttributeHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeProviderTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    protected $eavConfig;

    /**
     * @var AttributeProvider
     */
    protected $object;

    protected function setUp(): void
    {
        $this->eavConfig = $this->getMockBuilder(Config::class)
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

        /** @var Type|MockObject $collectionMock $entityType */
        $entityType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\ResourceModel\Customer|MockObject $entity */
        $entity = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Attribute|MockObject $attribute */
        $attribute = $this->getMockBuilder(Attribute::class)
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
            ['fields' => [
                $existentName => $existentField,
                $attrName => [
                    'name' => $attrName,
                    'handler' => AttributeHandler::class,
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

        /** @var Type|MockObject $collectionMock $entityType */
        $entityType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\ResourceModel\Customer|MockObject $entity */
        $entity = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Attribute|MockObject $attribute */
        $attribute = $this->getMockBuilder(Attribute::class)
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
            ['fields' => [
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

        /** @var Type|MockObject $collectionMock $entityType */
        $entityType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\ResourceModel\Customer|MockObject $entity */
        $entity = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Attribute|MockObject $attribute */
        $attribute = $this->getMockBuilder(Attribute::class)
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
            ['fields' => [
                $attrName => [
                    'name' => $attrName,
                    'handler' => AttributeHandler::class,
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
