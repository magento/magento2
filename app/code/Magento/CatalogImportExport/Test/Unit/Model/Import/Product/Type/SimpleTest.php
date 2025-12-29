<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\Type\Simple;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SimpleTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Product|MockObject
     */
    private $entityModel;

    /**
     * @var Simple
     */
    private $simpleType;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var Select|MockObject
     */
    private $select;

    protected function setUp(): void
    {
        $this->entityModel = $this->createMock(Product::class);
        $attrSetColFactory = $this->createMock(AttributeSetCollectionFactory::class);
        $attrColFactory = $this->createMock(AttributeCollectionFactory::class);
        $attrCollection = $this->createMock(AttributeCollection::class);

        $attribute = $this->createPartialMockWithReflection(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getIsVisible', 'getIsGlobal', 'getIsRequired', 'getIsUnique', 'getFrontendLabel',
             'getApplyTo', 'getDefaultValue', 'getUsesSource', 'isStatic']
        );
        $attribute->method('getIsVisible')->willReturn(true);
        $attribute->method('getIsGlobal')->willReturn(true);
        $attribute->method('getIsRequired')->willReturn(true);
        $attribute->method('getIsUnique')->willReturn(true);
        $attribute->method('getFrontendLabel')->willReturn('frontend_label');
        $attribute->method('getApplyTo')->willReturn(['simple']);
        $attribute->method('getDefaultValue')->willReturn('default_value');
        $attribute->method('getUsesSource')->willReturn(true);
        $attribute->method('isStatic')->willReturn(false);
        $entityAttributes = [
            [
                'attribute_id' => '1',
                'attribute_set_name' => 'attributeSetName',
            ],
            [
                'attribute_id' => '2',
                'attribute_set_name' => 'attributeSetName'
            ],
            [
                'attribute_id' => '3',
                'attribute_set_name' => 'attributeSetName'
            ],
        ];
        $attribute1 = $this->createPartialMockWithReflection(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getId', 'getAttributeCode', 'getFrontendInput', 'isStatic']
        );
        $attribute1->method('getId')->willReturn('1');
        $attribute1->method('getAttributeCode')->willReturn('attr_code');
        $attribute1->method('getFrontendInput')->willReturn('multiselect');
        $attribute1->method('isStatic')->willReturn(true);
        
        $attribute2 = $this->createPartialMockWithReflection(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getId', 'getAttributeCode', 'getFrontendInput', 'isStatic', 'getIsRequired', 'getIsUnique',
             'getDefaultValue', 'getUsesSource', 'getIsVisible', 'getApplyTo', 'getIsGlobal']
        );
        $attribute2->method('getId')->willReturn('2');
        $attribute2->method('getAttributeCode')->willReturn('boolean_attribute');
        $attribute2->method('getFrontendInput')->willReturn('boolean');
        $attribute2->method('isStatic')->willReturn(false);
        $attribute2->method('getIsRequired')->willReturn(true);
        $attribute2->method('getIsUnique')->willReturn(true);
        $attribute2->method('getDefaultValue')->willReturn('default_value');
        $attribute2->method('getUsesSource')->willReturn(true);
        $attribute2->method('getIsVisible')->willReturn(true);
        $attribute2->method('getApplyTo')->willReturn(['simple']);
        $attribute2->method('getIsGlobal')->willReturn(true);
        
        $attribute3 = $this->createPartialMockWithReflection(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getId', 'getAttributeCode', 'getFrontendInput', 'isStatic', 'getIsRequired', 'getIsUnique',
             'getDefaultValue', 'getUsesSource', 'getIsVisible', 'getApplyTo', 'getIsGlobal']
        );
        $attribute3->method('getId')->willReturn('3');
        $attribute3->method('getAttributeCode')->willReturn('text_attribute');
        $attribute3->method('getFrontendInput')->willReturn('text');
        $attribute3->method('isStatic')->willReturn(false);
        $attribute3->method('getIsRequired')->willReturn(true);
        $attribute3->method('getIsUnique')->willReturn(true);
        $attribute3->method('getDefaultValue')->willReturn('default_value');
        $attribute3->method('getUsesSource')->willReturn(true);
        $attribute3->method('getIsVisible')->willReturn(true);
        $attribute3->method('getApplyTo')->willReturn(['simple']);
        $attribute3->method('getIsGlobal')->willReturn(true);
        $callCount = 0;
        $this->entityModel->method('getEntityTypeId')
            ->willReturn(3);
        $this->entityModel->method('getAttributeOptions')
            ->willReturnCallback(function ($attribute) use (&$callCount) {
                $callCount++;
                if ($attribute->getAttributeCode() === 'boolean_attribute') {
                    return ['yes' => 1, 'no' => 0];
                }
                return ['option1', 'option2'];
            });
        $attrColFactory->method('create')
            ->willReturn($attrCollection);
        $attrCollection->method('setAttributeSetFilter')
            ->willReturn([$attribute1, $attribute2, $attribute3]);
        $attrCollection->method('addFieldToFilter')
            ->willReturnSelf();
        
        $getItemsCallCount = 0;
        $attrCollection->method('getItems')
            ->willReturnCallback(
                function () use (&$getItemsCallCount, $attribute1, $attribute2, $attribute3) {
                    $getItemsCallCount++;
                    return $getItemsCallCount === 1 ? [$attribute1, $attribute2, $attribute3] : [];
                }
            );

        $this->connection = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->select = $this->createPartialMock(
            Select::class,
            [
                'from',
                'where',
                'joinLeft',
                'getConnection',
            ]
        );
        $this->select->method('from')
            ->willReturnSelf();
        $this->select->method('where')
            ->willReturnSelf();
        $this->select->method('joinLeft')
            ->willReturnSelf();
        $this->connection->method('select')->willReturn($this->select);
        $this->connection->method('fetchAll')->willReturn($entityAttributes);
        $this->connection->method('getConnection')->willReturnSelf(); // Prevent actual DB connection
        
        $connection = $this->createMock(Mysql::class);
        $connection->method('quoteInto')->willReturn('query');
        $this->select->method('getConnection')->willReturn($connection);
        $this->resource = $this->createPartialMock(
            ResourceConnection::class,
            [
                'getConnection',
                'getTableName',
            ]
        );
        $this->resource->method('getConnection')
            ->willReturn($this->connection);
        $this->resource->method('getTableName')
            ->willReturn('tableName');
        // Create minimal ObjectManager mock
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
        
        // Instantiate Simple class directly with dependencies
        $this->simpleType = new Simple(
            $attrSetColFactory,
            $attrColFactory,
            $this->resource,
            [$this->entityModel, 'simple']
        );
    }

    /**
     * Because AbstractType has static member variables,  we must clean them in between tests.
     * Luckily they are publicly accessible.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Simple::$commonAttributesCache = [];
        Simple::$invAttributesCache = [];
        Simple::$attributeCodeToId = [];
    }

    #[DataProvider('addAttributeOptionDataProvider')]
    public function testAddAttributeOption($code, $optionKey, $optionValue, $initAttributes, $resultAttributes)
    {
        $this->setPropertyValue($this->simpleType, '_attributes', $initAttributes);

        $this->simpleType->addAttributeOption($code, $optionKey, $optionValue);

        $this->assertEquals($resultAttributes, $this->getPropertyValue($this->simpleType, '_attributes'));
    }

    public function testAddAttributeOptionReturn()
    {
        $code = 'attr set name value key';
        $optionKey = 'option key';
        $optionValue = 'option value';

        $result = $this->simpleType->addAttributeOption($code, $optionKey, $optionValue);

        $this->assertEquals($result, $this->simpleType);
    }

    public function testGetCustomFieldsMapping()
    {
        $expectedResult = ['value'];
        $this->setPropertyValue($this->simpleType, '_customFieldsMapping', $expectedResult);

        $result = $this->simpleType->getCustomFieldsMapping();

        $this->assertEquals($expectedResult, $result);
    }

    public function testIsRowValidSuccess()
    {
        $rowData = ['_attribute_set' => 'attribute_set_name'];
        $rowNum = 1;
        $this->entityModel->method('getRowScope')
            ->willReturn(null);
        $this->entityModel->expects($this->never())
            ->method('addRowError');
        $this->setPropertyValue(
            $this->simpleType,
            '_attributes',
            [
                $rowData[Product::COL_ATTR_SET] => [],
            ]
        );
        $this->assertTrue($this->simpleType->isRowValid($rowData, $rowNum));
    }

    public function testIsRowValidError()
    {
        $rowData = [
            '_attribute_set' => 'attribute_set_name',
            'sku' => 'sku',
            'attr_code' => 'test'
        ];
        $rowNum = 1;
        $this->entityModel->method('getRowScope')
            ->willReturn(1);
        $this->entityModel->method('addRowError')
            ->with(
                RowValidatorInterface::ERROR_VALUE_IS_REQUIRED,
                1,
                'attr_code'
            )
            ->willReturnSelf();
        $this->setPropertyValue(
            $this->simpleType,
            '_attributes',
            [
                $rowData[Product::COL_ATTR_SET] => [
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
    public static function addAttributeOptionDataProvider()
    {
        return [
            [
                'code' => 'attr set name value key',
                'optionKey' => 'option key',
                'optionValue' => 'option value',
                'initAttributes' => [
                    'attr set name' => [
                        'attr set name value key' => [],
                    ],
                ],
                'resultAttributes' => [
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
                'code' => 'attr set name value key',
                'optionKey' => 'option key',
                'optionValue' => 'option value',
                'initAttributes' => [
                    'attr set name' => [
                        'not equal to code value' => [],
                    ],
                ],
                'resultAttributes' => [
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
        $attributes = [
            'attributeSetName' => [
                'boolean_attribute' => [
                    'type' => 'boolean',
                    'is_static' => false,
                    'options' => ['yes' => 1, 'no' => 0],
                    'default_value' => null,
                ],
                'text_attribute' => [
                    'type' => 'text',
                    'is_static' => false,
                    'options' => [],
                    'default_value' => 'default_value',
                ],
            ],
        ];
        $this->setPropertyValue($this->simpleType, '_attributes', $attributes);

        $rowData = [
            '_attribute_set' => 'attributeSetName',
            'boolean_attribute' => 'yes',
        ];
        $expected = [
            'boolean_attribute' => 1,
            'text_attribute' => 'default_value'
        ];
        $result = $this->simpleType->prepareAttributesWithDefaultValueForSave($rowData);
        $this->assertEquals($expected, $result);
    }
}
