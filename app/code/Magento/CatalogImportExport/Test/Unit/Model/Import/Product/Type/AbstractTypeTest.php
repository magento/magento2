<?php declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType as AbstractType;
use Magento\CatalogImportExport\Model\Import\Product\Type\Simple;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
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
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class,
            ['create']
        );
        $attrSetCollection = $this->createMock(Collection::class);
        $attrColFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
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
            ->addMethods(['getIsVisible', 'getIsGlobal', 'getFrontendLabel', 'getApplyTo'])
            ->onlyMethods(
                [
                    'getAttributeCode',
                    'getId',
                    'getIsRequired',
                    'getIsUnique',
                    'isStatic',
                    'getDefaultValue',
                    'usesSource',
                    'getFrontendInput'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
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
                        'in' => [
                            'attribute_id',
                            'boolean_attribute',
                        ],
                    ],
                    [
                        'in' => [
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
        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();
        $this->select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $this->connection->expects($this->any())->method('select')->willReturn($this->select);
        $connection = $this->createMock(Mysql::class);
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
            ResourceConnection::class,
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
            Simple::class,
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
        $this->entityModel->expects($this->any())->method('getRowScope')->willReturn(1);
        $this->entityModel->expects($this->once())->method('addRowError')
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
