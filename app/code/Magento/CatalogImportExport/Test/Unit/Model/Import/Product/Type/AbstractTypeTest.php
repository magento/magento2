<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType as AbstractType;

/**
 * Test class for import product AbstractType class
 *
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AbstractTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var AbstractType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $abstractType;

    protected function setUp()
    {
        $this->entityModel = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product',
            [],
            [],
            '',
            false
        );
        $attrSetColFactory = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $attrSetCollection = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection',
            [],
            [],
            '',
            false
        );
        $attrColFactory = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $attributeSet = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            [],
            [],
            '',
            false
        );
        $attrCollection = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Collection',
            [
                'addFieldToFilter',
            ],
            [],
            '',
            false
        );
        $attribute = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute',
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
            ],
            [],
            '',
            false
        );

        $entityAttributes = [
            'attribute_id' => 'attributeSetName'
        ];

        $this->entityModel->expects($this->any())->method('getEntityTypeId')->willReturn(3);
        $this->entityModel->expects($this->any())->method('getAttributeOptions')->willReturn(['option1', 'option2']);
        $attrSetColFactory->expects($this->any())->method('create')->willReturn($attrSetCollection);
        $attrSetCollection->expects($this->any())->method('setEntityTypeFilter')->willReturn([$attributeSet]);
        $attrColFactory->expects($this->any())->method('create')->willReturn($attrCollection);
        $attrCollection->expects($this->any())->method('setAttributeSetFilter')->willReturn([$attribute]);
        $attributeSet->expects($this->any())->method('getId')->willReturn(1);
        $attributeSet->expects($this->any())->method('getAttributeSetName')->willReturn('attribute_set_name');
        $attribute->expects($this->any())->method('getAttributeCode')->willReturn('attr_code');
        $attribute->expects($this->any())->method('getId')->willReturn('1');
        $attribute->expects($this->any())->method('getIsVisible')->willReturn(true);
        $attribute->expects($this->any())->method('getIsGlobal')->willReturn(true);
        $attribute->expects($this->any())->method('getIsRequired')->willReturn(true);
        $attribute->expects($this->any())->method('getIsUnique')->willReturn(true);
        $attribute->expects($this->any())->method('getFrontendLabel')->willReturn('frontend_label');
        $attribute->expects($this->any())->method('isStatic')->willReturn(true);
        $attribute->expects($this->any())->method('getApplyTo')->willReturn(['simple']);
        $attribute->expects($this->any())->method('getDefaultValue')->willReturn('default_value');
        $attribute->expects($this->any())->method('usesSource')->willReturn(true);
        $attribute->expects($this->any())->method('getFrontendInput')->willReturn('multiselect');
        $attrCollection
            ->expects($this->any())
            ->method('addFieldToFilter')
            ->with(
                'main_table.attribute_id',
                ['in' => [
                    key($entityAttributes)
                ]]
            )
            ->willReturn([$attribute]);

        $this->connection = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [
                'select',
                'fetchAll',
                'fetchPairs',
                'joinLeft',
                'insertOnDuplicate',
                'delete',
                'quoteInto'
            ],
            [],
            '',
            false
        );
        $this->select = $this->getMock(
            'Magento\Framework\DB\Select',
            [
                'from',
                'where',
                'joinLeft',
                'getAdapter',
            ],
            [],
            '',
            false
        );
        $this->select->expects($this->any())->method('from')->will($this->returnSelf());
        $this->select->expects($this->any())->method('where')->will($this->returnSelf());
        $this->select->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $this->connection->expects($this->any())->method('select')->will($this->returnValue($this->select));
        $adapter = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $adapter->expects($this->any())->method('quoteInto')->will($this->returnValue('query'));
        $this->select->expects($this->any())->method('getAdapter')->willReturn($adapter);
        $this->connection->expects($this->any())->method('insertOnDuplicate')->willReturnSelf();
        $this->connection->expects($this->any())->method('delete')->willReturnSelf();
        $this->connection->expects($this->any())->method('quoteInto')->willReturn('');
        $this->connection
            ->expects($this->any())
            ->method('fetchPairs')
            ->will($this->returnValue($entityAttributes));

        $this->resource = $this->getMock(
            '\Magento\Framework\App\Resource',
            [
                'getConnection',
                'getTableName',
            ],
            [],
            '',
            false
        );
        $this->resource->expects($this->any())->method('getConnection')->will(
            $this->returnValue($this->connection)
        );
        $this->resource->expects($this->any())->method('getTableName')->will(
            $this->returnValue('tableName')
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->simpleType = $this->objectManagerHelper->getObject(
            'Magento\CatalogImportExport\Model\Import\Product\Type\Simple',
            [
                'attrSetColFac' => $attrSetColFactory,
                'prodAttrColFac' => $attrColFactory,
                'params' => [$this->entityModel, 'simple'],
                'resource' => $this->resource,
            ]
        );

        $this->abstractType = $this->getMockBuilder(
            '\Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType'
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
        $this->setPropertyValue($this->simpleType, '_attributes', [
            $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET] => [],
        ]);
        $this->assertTrue($this->simpleType->isRowValid($rowData, $rowNum));
    }

    public function testIsRowValidError()
    {
        $rowData = [
            '_attribute_set' => 'attribute_set_name',
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
        $this->setPropertyValue($this->simpleType, '_attributes', [
            $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET] => [
                'attr_code' => [
                    'is_required' => true,
                ],
            ],
        ]);

        $this->assertFalse($this->simpleType->isRowValid($rowData, $rowNum));
    }

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
}
