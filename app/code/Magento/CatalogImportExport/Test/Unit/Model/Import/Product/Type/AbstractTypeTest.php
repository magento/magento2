<?php declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType as AbstractType;
use Magento\CatalogImportExport\Model\Import\Product\Type\Simple;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for import product AbstractType class
 *
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTypeTest extends TestCase
{
    /**
     * @var Product|MockObject
     */
    protected $entityModel;

    /**
     * @var Simple
     */
    protected $simpleType;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resource;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

    /**
     * @var Select|MockObject
     */
    protected $select;

    /**
     * @var AbstractType|MockObject
     */
    protected $abstractType;

    protected function setUp(): void
    {
        $this->entityModel = $this->createMock(Product::class);
        $attrSetColFactory = $this->createPartialMock(
            AttributeSetCollectionFactory::class,
            ['create']
        );
        $attrSetCollection = $this->createMock(Collection::class);
        $attrColFactory = $this->createPartialMock(
            AttributeCollectionFactory::class,
            ['create']
        );
        $attributeSet = $this->createMock(Set::class);
        $attrCollection = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class,
            [
                'addFieldToFilter',
                'setAttributeSetFilter'
            ]
        );
        $attribute = $this->getMockBuilder(Attribute::class)
            ->addMethods(['getFrontendLabel'])
            ->onlyMethods(
                [
                    'getAttributeCode',
                    'getId',
                    'getIsRequired',
                    'getIsUnique',
                    'isStatic',
                    'getDefaultValue',
                    'usesSource',
                    'getFrontendInput',
                    'getIsVisible',
                    'getApplyTo',
                    'getIsGlobal',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->method('getIsVisible')
            ->willReturn(true);
        $attribute->method('getIsGlobal')
            ->willReturn(true);
        $attribute->method('getIsRequired')
            ->willReturn(true);
        $attribute->method('getIsUnique')
            ->willReturn(true);
        $attribute->method('getFrontendLabel')
            ->willReturn('frontend_label');
        $attribute->method('getApplyTo')
            ->willReturn(['simple']);
        $attribute->method('getDefaultValue')
            ->willReturn('default_value');
        $attribute->method('usesSource')
            ->willReturn(true);
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
        $attribute1 = clone $attribute;
        $attribute2 = clone $attribute;
        $attribute3 = clone $attribute;
        $attribute1->method('getId')
            ->willReturn('1');
        $attribute1->method('getAttributeCode')
            ->willReturn('attr_code');
        $attribute1->method('getFrontendInput')
            ->willReturn('multiselect');
        $attribute1->method('isStatic')
            ->willReturn(true);
        $attribute2->method('getId')
            ->willReturn('2');
        $attribute2->method('getAttributeCode')
            ->willReturn('boolean_attribute');
        $attribute2->method('getFrontendInput')
            ->willReturn('boolean');
        $attribute2->method('isStatic')
            ->willReturn(false);
        $attribute3->method('getId')
            ->willReturn('3');
        $attribute3->method('getAttributeCode')
            ->willReturn('Text_attribute');
        $attribute3->method('getFrontendInput')
            ->willReturn('text');
        $attribute3->method('isStatic')
            ->willReturn(false);
        $this->entityModel->method('getEntityTypeId')
            ->willReturn(3);
        $this->entityModel->method('getAttributeOptions')
            ->willReturnCallback(function () use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    return ['option1', 'option2'];
                } elseif ($callCount === 2) {
                    return ['yes' => 1, 'no' => 0];
                }
            });
        $attrSetColFactory->method('create')
            ->willReturn($attrSetCollection);
        $attrSetCollection->method('setEntityTypeFilter')
            ->willReturn([$attributeSet]);
        $attrColFactory->method('create')
            ->willReturn($attrCollection);
        $attrCollection->method('setAttributeSetFilter')
            ->willReturn([$attribute1, $attribute2, $attribute3]);
        $attributeSet->method('getId')
            ->willReturn(1);
        $attributeSet->method('getAttributeSetName')
            ->willReturn('attribute_set_name');
        $attrCollection->method('addFieldToFilter')
        ->willReturnCallback(function ($field, $conditions) use ($attribute1, $attribute2, $attribute3) {
            if ($field === ['main_table.attribute_id'] && $conditions === [['in' => ['1', '2', '3']]]) {
                return [$attribute1, $attribute2, $attribute3];
            } elseif ($field === ['main_table.attribute_code'] &&
                $conditions === [['in' =>
                    ['related_tgtr_position_behavior',
                        'related_tgtr_position_limit',
                        'upsell_tgtr_position_behavior',
                        'upsell_tgtr_position_limit',
                        'thumbnail_label',
                        'small_image_label',
                        'image_label']]]) {
                return [];
            }
        });

        $this->connection = $this->getMockBuilder(Mysql::class)
            ->addMethods(['joinLeft'])
            ->onlyMethods(['select', 'fetchAll', 'fetchPairs', 'insertOnDuplicate', 'delete', 'quoteInto'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->connection->method('select')
            ->willReturn($this->select);
        $connection = $this->createMock(Mysql::class);
        $connection->method('quoteInto')
            ->willReturn('query');
        $this->select->method('getConnection')
            ->willReturn($connection);
        $this->connection->method('insertOnDuplicate')
            ->willReturnSelf();
        $this->connection->method('delete')
            ->willReturnSelf();
        $this->connection->method('quoteInto')
            ->willReturn('');
        $this->connection->method('fetchAll')
            ->willReturn($entityAttributes);
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
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->simpleType = $this->objectManagerHelper->getObject(
            Simple::class,
            [
                'attrSetColFac' => $attrSetColFactory,
                'prodAttrColFac' => $attrColFactory,
                'params' => [$this->entityModel, 'simple'],
                'resource' => $this->resource,
            ]
        );
        $this->abstractType = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * Because AbstractType has static member variables,  we must clean them in between tests.
     * Luckily they are publicly accessible.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        AbstractType::$commonAttributesCache = [];
        AbstractType::$invAttributesCache = [];
        AbstractType::$attributeCodeToId = [];
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
            'sku' => 'sku'
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
