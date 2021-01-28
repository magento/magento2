<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType as AbstractType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test class for import product AbstractType class
 *
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityModel;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\Simple
     */
    protected $simpleType;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $select;

    /**
     * @var AbstractType|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $abstractType;

    protected function setUp(): void
    {
        $this->entityModel = $this->createMock(\Magento\CatalogImportExport\Model\Import\Product::class);
        $attrSetColFactory = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class,
            ['create']
        );
        $attrSetCollection = $this->createMock(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::class);
        $attrColFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
            ['create']
        );
        $attributeSet = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Set::class);
        $attrCollection = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class,
            [
                'addFieldToFilter',
                'setAttributeSetFilter'
            ]
        );
        $attribute = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute::class,
            [
                'getAttributeCode',
                'getId',
                'getIsVisible',
                'getIsGlobal',
                'getIsRequired',
                'getIsUnique',
                'getFrontendLabel',
                'isStatic',
                'getApplyTo',
                'getDefaultValue',
                'usesSource',
                'getFrontendInput',
            ]
        );
        $attribute->expects($this->any())->method('getIsVisible')->willReturn(true);
        $attribute->expects($this->any())->method('getIsGlobal')->willReturn(true);
        $attribute->expects($this->any())->method('getIsRequired')->willReturn(true);
        $attribute->expects($this->any())->method('getIsUnique')->willReturn(true);
        $attribute->expects($this->any())->method('getFrontendLabel')->willReturn('frontend_label');
        $attribute->expects($this->any())->method('getApplyTo')->willReturn(['simple']);
        $attribute->expects($this->any())->method('getDefaultValue')->willReturn('default_value');
        $attribute->expects($this->any())->method('usesSource')->willReturn(true);

        $entityAttributes = [
            [
                'attribute_id' => 'attribute_id',
                'attribute_set_name' => 'attributeSetName',
            ],
            [
                'attribute_id' => 'boolean_attribute',
                'attribute_set_name' => 'attributeSetName'
            ]
        ];
        $attribute1 = clone $attribute;
        $attribute2 = clone $attribute;
        $attribute3 = clone $attribute;

        $attribute1->expects($this->any())->method('getId')->willReturn('1');
        $attribute1->expects($this->any())->method('getAttributeCode')->willReturn('attr_code');
        $attribute1->expects($this->any())->method('getFrontendInput')->willReturn('multiselect');
        $attribute1->expects($this->any())->method('isStatic')->willReturn(true);

        $attribute2->expects($this->any())->method('getId')->willReturn('2');
        $attribute2->expects($this->any())->method('getAttributeCode')->willReturn('boolean_attribute');
        $attribute2->expects($this->any())->method('getFrontendInput')->willReturn('boolean');
        $attribute2->expects($this->any())->method('isStatic')->willReturn(false);

        $attribute3->expects($this->any())->method('getId')->willReturn('3');
        $attribute3->expects($this->any())->method('getAttributeCode')->willReturn('text_attribute');
        $attribute3->expects($this->any())->method('getFrontendInput')->willReturn('text');
        $attribute3->expects($this->any())->method('isStatic')->willReturn(false);

        $this->entityModel->expects($this->any())->method('getEntityTypeId')->willReturn(3);
        $this->entityModel->expects($this->any())->method('getAttributeOptions')->willReturnOnConsecutiveCalls(
            ['option1', 'option2'],
            ['yes' => 1, 'no' => 0]
        );
        $attrSetColFactory->expects($this->any())->method('create')->willReturn($attrSetCollection);
        $attrSetCollection->expects($this->any())->method('setEntityTypeFilter')->willReturn([$attributeSet]);
        $attrColFactory->expects($this->any())->method('create')->willReturn($attrCollection);
        $attrCollection->expects($this->any())
            ->method('setAttributeSetFilter')
            ->willReturn([$attribute1, $attribute2, $attribute3]);
        $attributeSet->expects($this->any())->method('getId')->willReturn(1);
        $attributeSet->expects($this->any())->method('getAttributeSetName')->willReturn('attribute_set_name');

        $attrCollection
            ->expects($this->any())
            ->method('addFieldToFilter')
            ->with(
                ['main_table.attribute_id', 'main_table.attribute_code'],
                [
                    [
                        'in' =>
                            [
                                'attribute_id',
                                'boolean_attribute',
                            ],
                    ],
                    [
                        'in' =>
                            [
                                'related_tgtr_position_behavior',
                                'related_tgtr_position_limit',
                                'upsell_tgtr_position_behavior',
                                'upsell_tgtr_position_limit',
                                'thumbnail_label',
                                'small_image_label',
                                'image_label',
                            ],
                    ],
                ]
            )
            ->willReturn([$attribute1, $attribute2, $attribute3]);

        $this->connection = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            [
                'select',
                'fetchAll',
                'fetchPairs',
                'joinLeft',
                'insertOnDuplicate',
                'delete',
                'quoteInto'
            ]
        );
        $this->select = $this->createPartialMock(
            \Magento\Framework\DB\Select::class,
            [
                'from',
                'where',
                'joinLeft',
                'getConnection',
            ]
        );
        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();
        $this->select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $this->connection->expects($this->any())->method('select')->willReturn($this->select);
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $connection->expects($this->any())->method('quoteInto')->willReturn('query');
        $this->select->expects($this->any())->method('getConnection')->willReturn($connection);
        $this->connection->expects($this->any())->method('insertOnDuplicate')->willReturnSelf();
        $this->connection->expects($this->any())->method('delete')->willReturnSelf();
        $this->connection->expects($this->any())->method('quoteInto')->willReturn('');
        $this->connection
            ->expects($this->any())
            ->method('fetchAll')
            ->willReturn($entityAttributes);

        $this->resource = $this->createPartialMock(
            \Magento\Framework\App\ResourceConnection::class,
            [
                'getConnection',
                'getTableName',
            ]
        );
        $this->resource->expects($this->any())->method('getConnection')->willReturn(
            $this->connection
        );
        $this->resource->expects($this->any())->method('getTableName')->willReturn(
            'tableName'
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->simpleType = $this->objectManagerHelper->getObject(
            \Magento\CatalogImportExport\Model\Import\Product\Type\Simple::class,
            [
                'attrSetColFac' => $attrSetColFactory,
                'prodAttrColFac' => $attrColFactory,
                'params' => [$this->entityModel, 'simple'],
                'resource' => $this->resource,
            ]
        );

        $this->abstractType = $this->getMockBuilder(
            \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class
        )
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();
    }

    /**
     * @dataProvider addAttributeOptionDataProvider
     */
    public function testAddAttributeOption($code, $optionKey, $optionValue, $initAttributes, $resultAttributes)
    {
        $this->setPropertyValue($this->abstractType, '_attributes', $initAttributes);

        $this->abstractType->addAttributeOption($code, $optionKey, $optionValue);

        $this->assertEquals($resultAttributes, $this->getPropertyValue($this->abstractType, '_attributes'));
    }

    public function testAddAttributeOptionReturn()
    {
        $code = 'attr set name value key';
        $optionKey = 'option key';
        $optionValue = 'option value';

        $result = $this->abstractType->addAttributeOption($code, $optionKey, $optionValue);

        $this->assertEquals($result, $this->abstractType);
    }

    public function testGetCustomFieldsMapping()
    {
        $expectedResult = ['value'];
        $this->setPropertyValue($this->abstractType, '_customFieldsMapping', $expectedResult);

        $result = $this->abstractType->getCustomFieldsMapping();

        $this->assertEquals($expectedResult, $result);
    }

    public function testIsRowValidSuccess()
    {
        $rowData = ['_attribute_set' => 'attribute_set_name'];
        $rowNum = 1;
        $this->entityModel->expects($this->any())->method('getRowScope')->willReturn(null);
        $this->entityModel->expects($this->never())->method('addRowError');
        $this->setPropertyValue(
            $this->simpleType,
            '_attributes',
            [
                $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET] => [],
            ]
        );
        $this->assertTrue($this->simpleType->isRowValid($rowData, $rowNum));
    }

    public function testIsRowValidError()
    {
        $rowData = [
            '_attribute_set' => 'attribute_set_name',
            'sku' => 'sku'
        ];
        $rowNum = 1;
        $this->entityModel->expects($this->any())->method('getRowScope')->willReturn(1);
        $this->entityModel->expects($this->once())->method('addRowError')
            ->with(
                \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_VALUE_IS_REQUIRED,
                1,
                'attr_code'
            )
            ->willReturnSelf();
        $this->setPropertyValue(
            $this->simpleType,
            '_attributes',
            [
                $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET] => [
                    'attr_code' => [
                        'is_required' => true,
                    ],
                ],
            ]
        );

        $this->assertFalse($this->simpleType->isRowValid($rowData, $rowNum));
    }

    /**
     * @return array
     */
    public function addAttributeOptionDataProvider()
    {
        return [
            [
                '$code' => 'attr set name value key',
                '$optionKey' => 'option key',
                '$optionValue' => 'option value',
                '$initAttributes' => [
                    'attr set name' => [
                        'attr set name value key' => [],
                    ],
                ],
                '$resultAttributes' => [
                    'attr set name' => [
                        'attr set name value key' => [
                            'options' => [
                                'option key' => 'option value'
                            ]
                        ]
                    ],
                ],
            ],
            [
                '$code' => 'attr set name value key',
                '$optionKey' => 'option key',
                '$optionValue' => 'option value',
                '$initAttributes' => [
                    'attr set name' => [
                        'not equal to code value' => [],
                    ],
                ],
                '$resultAttributes' => [
                    'attr set name' => [
                        'not equal to code value' => [],
                    ],
                ]
            ],
        ];
    }

    /**
     * @param $object
     * @param $property
     * @return mixed
     */
    protected function getPropertyValue(&$object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }

    public function testPrepareAttributesWithDefaultValueForSave()
    {
        $rowData = [
            '_attribute_set' => 'attributeSetName',
            'boolean_attribute' => 'Yes',
        ];

        $expected = [
            'boolean_attribute' => 1,
            'text_attribute' => 'default_value'
        ];
        $result = $this->simpleType->prepareAttributesWithDefaultValueForSave($rowData);
        $this->assertEquals($expected, $result);
    }
}
