<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Rule\Test\Unit\Model\Condition\Product;

use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Product\AbstractProduct;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractProductTest extends TestCase
{
    /**
     * Tested condition
     *
     * @var AbstractProduct|MockObject
     */
    protected $_condition;

    /**
     * Framework object
     *
     * @var DataObject|MockObject
     */
    protected $_object;

    /**
     * Reflection for AbstractProduct::$_entityAttributeValues
     *
     * @var \ReflectionProperty
     */
    protected $_entityAttributeValuesProperty;

    /**
     * Reflection for AbstractProduct::$_config
     *
     * @var \ReflectionProperty
     */
    protected $_configProperty;

    /**
     * Reflection for AbstractProduct::$productCategoryListProperty
     *
     * @var \ReflectionProperty
     */
    private $productCategoryListProperty;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_condition = $this->getMockForAbstractClass(
            AbstractProduct::class,
            [],
            '',
            false
        );

        $this->productCategoryListProperty = new \ReflectionProperty(
            AbstractProduct::class,
            'productCategoryList'
        );
        $this->productCategoryListProperty->setAccessible(true);

        $this->_entityAttributeValuesProperty = new \ReflectionProperty(
            AbstractProduct::class,
            '_entityAttributeValues'
        );
        $this->_entityAttributeValuesProperty->setAccessible(true);

        $this->_configProperty = new \ReflectionProperty(
            AbstractProduct::class,
            '_config'
        );
        $this->_configProperty->setAccessible(true);
    }

    /**
     * Test to validate equal category id condition
     */
    public function testValidateAttributeEqualCategoryId()
    {
        $product = $this->getMockBuilder(AbstractModel::class)
            ->addMethods(["getAttribute"])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_condition->setAttribute('category_ids');
        $this->_condition->setValueParsed('1');
        $this->_condition->setOperator('{}');

        $productCategoryList = $this->getMockBuilder(ProductCategoryList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCategoryList->method('getCategoryIds')->willReturn([1, 2]);
        $this->productCategoryListProperty->setValue(
            $this->_condition,
            $productCategoryList
        );
        $this->_configProperty->setValue(
            $this->_condition,
            $this->getMockBuilder(Config::class)
                ->disableOriginalConstructor()
                ->getMock()
        );

        $this->assertTrue($this->_condition->validate($product));
    }

    /**
     * Test to validate empty attribute condition
     */
    public function testValidateEmptyEntityAttributeValues()
    {
        $product = $this->getMockBuilder(AbstractModel::class)
            ->addMethods(["getAttribute"])
            ->onlyMethods(['getResource'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $product->expects($this->once())
            ->method('getResource')
            ->willReturn(null);
        $product->setId(1);
        $configProperty = new \ReflectionProperty(
            AbstractProduct::class,
            '_entityAttributeValues'
        );
        $configProperty->setAccessible(true);
        $configProperty->setValue($this->_condition, []);
        $this->assertFalse($this->_condition->validate($product));
    }

    /**
     * Test to validate empty attribute value condition
     */
    public function testValidateEmptyEntityAttributeValuesWithResource()
    {
        $product = $this->getMockBuilder(AbstractModel::class)
            ->addMethods(["getAttribute"])
            ->onlyMethods(['getResource'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $product->setId(1);
        $time = '04/19/2012 11:59 am';
        $product->setData('someAttribute', $time);
        $this->_condition->setAttribute('someAttribute');
        $this->_entityAttributeValuesProperty->setValue($this->_condition, []);

        $this->_configProperty->setValue(
            $this->_condition,
            $this->createMock(Config::class)
        );

        $attribute = new DataObject();
        $attribute->setBackendType('datetime');

        $newResource = $this->createPartialMock(Product::class, ['getAttribute']);
        $newResource->expects($this->any())
            ->method('getAttribute')
            ->with('someAttribute')
            ->willReturn($attribute);
        $product->expects($this->atLeastOnce())
            ->method('getResource')
            ->willReturn($newResource);

        $this->assertFalse($this->_condition->validate($product));

        $product->setData('someAttribute', 'option1,option2,option3');
        $attribute->setBackendType('null');
        $attribute->setFrontendInput('multiselect');

        $newResource = $this->createPartialMock(Product::class, ['getAttribute']);
        $newResource->expects($this->any())
            ->method('getAttribute')
            ->with('someAttribute')
            ->willReturn($attribute);

        $product->setResource($newResource);
        $this->assertFalse($this->_condition->validate($product));
    }

    /**
     * Test to validate set entity attribute value with resource condition
     */
    public function testValidateSetEntityAttributeValuesWithResource()
    {
        $this->_condition->setAttribute('someAttribute');
        $product = $this->getMockBuilder(AbstractModel::class)
            ->addMethods(['getAttribute'])
            ->onlyMethods(['getResource'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $product->setAtribute('attribute');
        $product->setData('someAttribute', '');
        $product->setId(12);

        $this->_configProperty->setValue(
            $this->_condition,
            $this->createMock(Config::class)
        );
        $this->_entityAttributeValuesProperty->setValue(
            $this->_condition,
            $this->createMock(Config::class)
        );

        $attribute = new DataObject();
        $attribute->setBackendType('datetime');

        $newResource = $this->createPartialMock(Product::class, ['getAttribute']);
        $newResource->expects($this->any())
            ->method('getAttribute')
            ->with('someAttribute')
            ->willReturn($attribute);

        $product->expects($this->atLeastOnce())
            ->method('getResource')
            ->willReturn($newResource);

        $this->_entityAttributeValuesProperty->setValue(
            $this->_condition,
            [
                1 => ['Dec. 1979 17:30'],
                2 => ['Dec. 1979 17:30'],
                3 => ['Dec. 1979 17:30']
            ]
        );
        $this->assertFalse($this->_condition->validate($product));
    }

    /**
     * Test to validate set entity attribute value without resource condition
     */
    public function testValidateSetEntityAttributeValuesWithoutResource()
    {
        $product = $this->getMockBuilder(AbstractModel::class)
            ->addMethods(['someMethod'])
            ->onlyMethods(['getResource', 'load'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_condition->setAttribute('someAttribute');
        $product->setAtribute('attribute');
        $product->setId(12);

        $this->_configProperty->setValue(
            $this->_condition,
            $this->createMock(Config::class)
        );

        $this->_entityAttributeValuesProperty->setValue(
            $this->_condition,
            $this->createMock(Config::class)
        );

        $attribute = new DataObject();
        $attribute->setBackendType('multiselect');

        $newResource = $this->createPartialMock(Product::class, ['getAttribute']);
        $newResource->expects($this->any())
            ->method('getAttribute')
            ->with('someAttribute')
            ->willReturn($attribute);

        $product->expects($this->atLeastOnce())
            ->method('getResource')
            ->willReturn($newResource);

        $this->_entityAttributeValuesProperty->setValue(
            $this->_condition,
            [
                1 => [''],
                2 => ['option1,option2,option3'],
                3 => ['option1,option2,option3']
            ]
        );

        $this->assertFalse($this->_condition->validate($product));

        $attribute = new DataObject();
        $attribute->setBackendType(null);
        $attribute->setFrontendInput('multiselect');

        $newResource = $this->createPartialMock(Product::class, ['getAttribute']);
        $newResource->expects($this->any())
            ->method('getAttribute')
            ->with('someAttribute')
            ->willReturn($attribute);

        $product->setResource($newResource);
        $product->setId(1);
        $product->setData('someAttribute', 'value');

        $this->assertFalse($this->_condition->validate($product));
    }

    /**
     * Test to get tables to join
     */
    public function testGetjointTables()
    {
        $this->_condition->setAttribute('category_ids');
        $this->assertEquals([], $this->_condition->getTablesToJoin());
    }

    /**
     * Test to get mapped sql field
     */
    public function testGetMappedSqlField()
    {
        $this->_condition->setAttribute('category_ids');
        $this->assertEquals('e.entity_id', $this->_condition->getMappedSqlField());
        $this->_condition->setAttribute('attribute_set_id');
        $this->assertEquals('e.attribute_set_id', $this->_condition->getMappedSqlField());
    }

    /**
     * Test to prepare value options
     *
     * @param array $setData
     * @param string $attributeObjectFrontendInput
     * @param array $attrObjectSourceAllOptionsValue
     * @param array $attrSetCollectionOptionsArray
     * @param bool $expectedAttrObjSourceAllOptionsParam
     * @param array $expectedValueSelectOptions
     * @param array $expectedValueOption
     * @dataProvider prepareValueOptionsDataProvider
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testPrepareValueOptions(
        $setData,
        $attributeObjectFrontendInput,
        $attrObjectSourceAllOptionsValue,
        $attrSetCollectionOptionsArray,
        $expectedAttrObjSourceAllOptionsParam,
        $expectedValueSelectOptions,
        $expectedValueOption
    ) {
        foreach ($setData as $key => $value) {
            $this->_condition->setData($key, $value);
        }

        $attrObjectSourceMock = $this->getMockBuilder(AbstractSource::class)
            ->onlyMethods(['getAllOptions'])
            ->disableOriginalConstructor()
            ->getMock();
        $attrObjectSourceMock
            ->expects((null === $expectedAttrObjSourceAllOptionsParam) ? $this->never() : $this->once())
            ->method('getAllOptions')
            ->with($expectedAttrObjSourceAllOptionsParam, true)
            ->willReturn($attrObjectSourceAllOptionsValue);

        $attributeObjectMock = $this->getMockBuilder(Attribute::class)
            ->addMethods(['getAllOptions'])
            ->onlyMethods(['usesSource', 'getFrontendInput', 'getSource'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeObjectMock->method('usesSource')->willReturn(true);
        $attributeObjectMock
            ->expects((null === $attributeObjectFrontendInput) ? $this->never() : $this->once())
            ->method('getFrontendInput')
            ->willReturn($attributeObjectFrontendInput);
        $attributeObjectMock->method('getSource')->willReturn($attrObjectSourceMock);

        $entityTypeMock = $this->getMockBuilder(Type::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->method('getId')->willReturn('SomeEntityType');

        $configValueMock = $this->createPartialMock(
            Config::class,
            ['getAttribute', 'getEntityType']
        );
        $configValueMock->method('getAttribute')->willReturn($attributeObjectMock);
        $configValueMock->method('getEntityType')->willReturn($entityTypeMock);

        $configProperty = new \ReflectionProperty(
            AbstractProduct::class,
            '_config'
        );
        $configProperty->setAccessible(true);
        $configProperty->setValue($this->_condition, $configValueMock);

        $attrSetCollectionValueMock = $this
            ->getMockBuilder(Collection::class)
            ->onlyMethods(['setEntityTypeFilter', 'load', 'toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $attrSetCollectionValueMock->method('setEntityTypeFilter')->willReturnSelf();
        $attrSetCollectionValueMock->method('load')->willReturnSelf();
        $attrSetCollectionValueMock
            ->expects((null === $attrSetCollectionOptionsArray) ? $this->never() : $this->once())
            ->method('toOptionArray')
            ->willReturn($attrSetCollectionOptionsArray);

        $attrSetCollectionProperty = new \ReflectionProperty(
            AbstractProduct::class,
            '_attrSetCollection'
        );
        $attrSetCollectionProperty->setAccessible(true);
        $attrSetCollectionProperty->setValue($this->_condition, $attrSetCollectionValueMock);

        $testedMethod = new \ReflectionMethod(
            AbstractProduct::class,
            '_prepareValueOptions'
        );
        $testedMethod->setAccessible(true);
        $testedMethod->invoke($this->_condition);

        $this->assertEquals($expectedValueSelectOptions, $this->_condition->getData('value_select_options'));
        $this->assertEquals($expectedValueOption, $this->_condition->getData('value_option'));
    }

    /**
     * Data provider for prepare value options
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function prepareValueOptionsDataProvider()
    {
        return [
            [
                [
                    'value_select_options' => ['key' => 'value'],
                    'value_option' => ['k' => 'v'],
                ], null, null, null, null, ['key' => 'value'], ['k' => 'v'],
            ],
            [
                ['attribute' => 'attribute_set_id'],
                null,
                null,
                [
                    ['value' => 'value1', 'label' => 'Label for value 1'],
                    ['value' => 'value2', 'label' => 'Label for value 2'],
                ],
                null,
                [
                    ['value' => 'value1', 'label' => 'Label for value 1'],
                    ['value' => 'value2', 'label' => 'Label for value 2'],
                ],
                [
                    'value1' => 'Label for value 1',
                    'value2' => 'Label for value 2'
                ]
            ],
            [
                [
                    'value_select_options' => [
                        ['value' => 'value3', 'label' => 'Label for value 3'],
                        ['value' => 'value4', 'label' => 'Label for value 4'],
                    ],
                    'attribute' => 'type_id',
                ],
                null,
                null,
                null,
                null,
                [
                    ['value' => 'value3', 'label' => 'Label for value 3'],
                    ['value' => 'value4', 'label' => 'Label for value 4'],
                ],
                [
                    'value3' => 'Label for value 3',
                    'value4' => 'Label for value 4'
                ]
            ],
            [
                [
                    'value_select_options' => [
                        'value5' => 'Label for value 5',
                        'value6' => 'Label for value 6',
                    ],
                    'attribute' => 'type_id',
                ],
                null,
                null,
                null,
                null,
                [
                    ['value' => 'value5', 'label' => 'Label for value 5'],
                    ['value' => 'value6', 'label' => 'Label for value 6'],
                ],
                [
                    'value5' => 'Label for value 5',
                    'value6' => 'Label for value 6'
                ]
            ],
            [
                [],
                'multiselect',
                [
                    ['value' => 'value7', 'label' => 'Label for value 7'],
                    ['value' => 'value8', 'label' => 'Label for value 8'],
                ],
                null,
                false,
                [
                    ['value' => 'value7', 'label' => 'Label for value 7'],
                    ['value' => 'value8', 'label' => 'Label for value 8'],
                ],
                [
                    'value7' => 'Label for value 7',
                    'value8' => 'Label for value 8',
                ],
            ],
            [
                [],
                'multiselect',
                [
                    ['value' => 'valueA', 'label' => 'Label for value A'],
                    ['value' => ['array value'], 'label' => 'Label for value B'],
                ],
                null,
                false,
                [
                    ['value' => 'valueA', 'label' => 'Label for value A'],
                    ['value' => ['array value'], 'label' => 'Label for value B'],
                ],
                [
                    'valueA' => 'Label for value A',
                ],
            ],
            [
                [],
                'select',
                [
                    ['value' => 'value7', 'label' => 'Label for value 7'],
                    ['value' => 'value8', 'label' => 'Label for value 8'],
                    ['value' => 'default', 'label' => 'Default Option']
                ],
                null,
                true,
                [
                    ['value' => 'value7', 'label' => 'Label for value 7'],
                    ['value' => 'value8', 'label' => 'Label for value 8'],
                    ['value' => 'default', 'label' => 'Default Option']
                ],
                [
                    'value7' => 'Label for value 7',
                    'value8' => 'Label for value 8',
                    'default' => 'Default Option'
                ],
            ]
        ];
    }
}
