<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Backend;

class SortbyTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_ATTRIBUTE_CODE = 'attribute_name';

    /**
     * @var \Magento\Catalog\Model\Category\Attribute\Backend\Sortby
     */
    protected $_model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected $_attribute;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $this->markTestSkipped('Due to MAGETWO-48956');
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_model = $this->_objectHelper->getObject(
            'Magento\Catalog\Model\Category\Attribute\Backend\Sortby',
            ['scopeConfig' => $this->_scopeConfig]
        );
        $this->_attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            [
                'getName',
                '__call',
                'isValueEmpty',
                'getEntity',
                'getFrontend',
                '__wakeup',
                'getIsRequired',
                'getIsUnique'
            ],
            [],
            '',
            false
        );

        $this->_model->setAttribute($this->_attribute);
    }

    /**
     * @param $attributeCode
     * @param $data
     * @param $expected
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave($attributeCode, $data, $expected)
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue($attributeCode));
        $object = new \Magento\Framework\DataObject($data);
        $this->_model->beforeSave($object);
        $this->assertTrue($object->hasData($attributeCode));
        $this->assertSame($expected, $object->getData($attributeCode));
    }

    public function beforeSaveDataProvider()
    {
        return [
            'attribute with specified value' => [
                self::DEFAULT_ATTRIBUTE_CODE,
                [self::DEFAULT_ATTRIBUTE_CODE => 'test_value'],
                'test_value',
            ],
            'attribute with default value' => [
                self::DEFAULT_ATTRIBUTE_CODE,
                [self::DEFAULT_ATTRIBUTE_CODE => null],
                null,
            ],
            'attribute does not exist' => [
                self::DEFAULT_ATTRIBUTE_CODE,
                [],
                null,
            ],
            'attribute sort by empty' => [
                'available_sort_by',
                ['available_sort_by' => null],
                null,
            ],
            'attribute sort by' => [
                'available_sort_by',
                ['available_sort_by' => ['test', 'value']],
                'test,value',
            ]
        ];
    }

    /**
     * @param $attributeCode
     * @param $data
     * @param $expected
     * @dataProvider afterLoadDataProvider
     */
    public function testAfterLoad($attributeCode, $data, $expected)
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue($attributeCode));
        $object = new \Magento\Framework\DataObject($data);
        $this->_model->afterLoad($object);
        $this->assertTrue($object->hasData($attributeCode));
        $this->assertSame($expected, $object->getData($attributeCode));
    }

    public function afterLoadDataProvider()
    {
        return [
            'attribute with specified value' => [
                self::DEFAULT_ATTRIBUTE_CODE,
                [self::DEFAULT_ATTRIBUTE_CODE => 'test_value'],
                'test_value',
            ],
            'attribute sort by empty' => [
                'available_sort_by',
                ['available_sort_by' => null],
                null,
            ],
            'attribute sort by' => [
                'available_sort_by',
                ['available_sort_by' => 'test,value'],
                ['test', 'value'],
            ]
        ];
    }

    /**
     * @param $attributeData
     * @param $data
     * @param $expected
     * @dataProvider validateDataProvider
     */
    public function testValidate($attributeData, $data, $expected)
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue($attributeData['code']));
        $this->_attribute
            ->expects($this->at(1))
            ->method('getIsRequired')
            ->will($this->returnValue($attributeData['isRequired']));
        $this->_attribute
            ->expects($this->any())
            ->method('isValueEmpty')
            ->will($this->returnValue($attributeData['isValueEmpty']));
        $object = new \Magento\Framework\DataObject($data);
        $this->assertSame($expected, $this->_model->validate($object));
    }

    public function validateDataProvider()
    {
        return [
            'is not required' => [
                ['code' => self::DEFAULT_ATTRIBUTE_CODE, 'isRequired' => false, 'isValueEmpty' => false],
                [],
                true,
            ],
            'required, empty, not use config case 1' => [
                ['code' => self::DEFAULT_ATTRIBUTE_CODE, 'isRequired' => true, 'isValueEmpty' => true],
                [self::DEFAULT_ATTRIBUTE_CODE => [], 'use_post_data_config' => []],
                false,
            ],
            'required, empty, not use config case 2' => [
                ['code' => self::DEFAULT_ATTRIBUTE_CODE, 'isRequired' => true, 'isValueEmpty' => true],
                [self::DEFAULT_ATTRIBUTE_CODE => [], 'use_post_data_config' => ['config']],
                false,
            ],
            'required, empty, use config' => [
                ['code' => self::DEFAULT_ATTRIBUTE_CODE, 'isRequired' => true, 'isValueEmpty' => true],
                [self::DEFAULT_ATTRIBUTE_CODE => [], 'use_post_data_config' => [self::DEFAULT_ATTRIBUTE_CODE]],
                true,
            ],
        ];
    }

    public function testValidateUnique()
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue('attribute_name'));
        $this->_attribute->expects($this->at(1))->method('getIsRequired');
        $this->_attribute->expects($this->at(2))->method('getIsUnique')->will($this->returnValue(true));

        $entityMock = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\AbstractEntity',
            [],
            '',
            false,
            true,
            true,
            ['checkAttributeUniqueValue']
        );
        $this->_attribute->expects($this->any())->method('getEntity')->will($this->returnValue($entityMock));
        $entityMock->expects($this->at(0))->method('checkAttributeUniqueValue')->will($this->returnValue(true));
        $this->assertTrue($this->_model->validate(new \Magento\Framework\DataObject()));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateUniqueException()
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue('attribute_name'));
        $this->_attribute->expects($this->at(1))->method('getIsRequired');
        $this->_attribute->expects($this->at(2))->method('getIsUnique')->will($this->returnValue(true));

        $entityMock = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\AbstractEntity',
            [],
            '',
            false,
            true,
            true,
            ['checkAttributeUniqueValue']
        );
        $frontMock = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend',
            [],
            '',
            false,
            true,
            true,
            ['getLabel']
        );
        $this->_attribute->expects($this->any())->method('getEntity')->will($this->returnValue($entityMock));
        $this->_attribute->expects($this->any())->method('getFrontend')->will($this->returnValue($frontMock));
        $entityMock->expects($this->at(0))->method('checkAttributeUniqueValue')->will($this->returnValue(false));
        $this->assertTrue($this->_model->validate(new \Magento\Framework\DataObject()));
    }

    /**
     * @param $attributeCode
     * @param $data
     * @dataProvider validateDefaultSortDataProvider
     */
    public function testValidateDefaultSort($attributeCode, $data)
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue($attributeCode));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue('value2'));
        $object = new \Magento\Framework\DataObject($data);
        $this->assertTrue($this->_model->validate($object));
    }

    public function validateDefaultSortDataProvider()
    {
        return [
            [
                'default_sort_by',
                [
                    'available_sort_by' => ['value1', 'value2'],
                    'default_sort_by' => 'value2',
                    'use_post_data_config' => []
                ],
            ],
            [
                'default_sort_by',
                [
                    'available_sort_by' => 'value1,value2',
                    'use_post_data_config' => ['default_sort_by']
                ]
            ],
            [
                'default_sort_by',
                [
                    'available_sort_by' => NULL,
                    'default_sort_by' => NULL,
                    'use_post_data_config' => ['available_sort_by', 'default_sort_by', 'filter_price_range']
                ]
            ],
        ];
    }

    /**
     * @param $attributeCode
     * @param $data
     * @dataProvider validateDefaultSortException
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateDefaultSortException($attributeCode, $data)
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue($attributeCode));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue('another value'));
        $object = new \Magento\Framework\DataObject($data);
        $this->_model->validate($object);
    }

    public function validateDefaultSortException()
    {
        return [
            [
                'default_sort_by',
                [
                    'available_sort_by' => NULL,
                    'use_post_data_config' => ['default_sort_by']
                ],
            ],
            [
                'default_sort_by',
                [
                    'available_sort_by' => NULL,
                    'use_post_data_config' => []
                ]
            ],
            [
                'default_sort_by',
                [
                    'available_sort_by' => ['value1', 'value2'],
                    'default_sort_by' => 'another value',
                    'use_post_data_config' => []
                ]
            ],
            [
                'default_sort_by',
                [
                    'available_sort_by' => 'value1',
                    'use_post_data_config' => []
                ]
            ],
        ];
    }
}
