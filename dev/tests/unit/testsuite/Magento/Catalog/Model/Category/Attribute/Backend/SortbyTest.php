<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Category\Attribute\Backend;

class SortbyTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_ATTRIBUTE_CODE = 'attribute_name';

    /**
     * @var \Magento\Catalog\Model\Category\Attribute\Backend\Sortby
     */
    protected $_model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
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
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_model = $this->_objectHelper->getObject(
            'Magento\Catalog\Model\Category\Attribute\Backend\Sortby',
            array('scopeConfig' => $this->_scopeConfig)
        );
        $this->_attribute = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            array(),
            '',
            false,
            true,
            true,
            array('getName', '__call', 'isValueEmpty', 'getEntity', 'getFrontend', '__wakeup')
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
        $object = new \Magento\Framework\Object($data);
        $this->_model->beforeSave($object);
        $this->assertTrue($object->hasData($attributeCode));
        $this->assertSame($expected, $object->getData($attributeCode));
    }

    public function beforeSaveDataProvider()
    {
        return array(
            'attribute with specified value' => array(
                self::DEFAULT_ATTRIBUTE_CODE,
                [self::DEFAULT_ATTRIBUTE_CODE => 'test_value'],
                'test_value'
            ),
            'attribute with default value' => array(
                self::DEFAULT_ATTRIBUTE_CODE,
                [self::DEFAULT_ATTRIBUTE_CODE => null],
                null
            ),
            'attribute does not exist' => array(
                self::DEFAULT_ATTRIBUTE_CODE,
                array(),
                false
            ),
            'attribute sort by empty' => array(
                'available_sort_by',
                ['available_sort_by' => null],
                ''
            ),
            'attribute sort by' => array(
                'available_sort_by',
                ['available_sort_by' => ['test', 'value']],
                'test,value'
            )
        );
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
        $object = new \Magento\Framework\Object($data);
        $this->_model->afterLoad($object);
        $this->assertTrue($object->hasData($attributeCode));
        $this->assertSame($expected, $object->getData($attributeCode));
    }

    public function afterLoadDataProvider()
    {
        return array(
            'attribute with specified value' => array(
                self::DEFAULT_ATTRIBUTE_CODE,
                [self::DEFAULT_ATTRIBUTE_CODE => 'test_value'],
                'test_value'
            ),
            'attribute sort by empty' => array(
                'available_sort_by',
                ['available_sort_by' => null],
                null
            ),
            'attribute sort by' => array(
                'available_sort_by',
                ['available_sort_by' => 'test,value'],
                ['test', 'value']
            )
        );
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
            ->method('__call')
            ->with('getIsRequired')
            ->will($this->returnValue($attributeData['isRequired']));
        $this->_attribute
            ->expects($this->any())
            ->method('isValueEmpty')
            ->will($this->returnValue($attributeData['isValueEmpty']));
        $object = new \Magento\Framework\Object($data);
        $this->assertSame($expected, $this->_model->validate($object));
    }

    public function validateDataProvider()
    {
        return array(
            'is not required' => array(
                ['code' => self::DEFAULT_ATTRIBUTE_CODE, 'isRequired' => false, 'isValueEmpty' => false],
                array(),
                true
            ),
            'required, empty, not use config case 1' => array(
                ['code' => self::DEFAULT_ATTRIBUTE_CODE, 'isRequired' => true, 'isValueEmpty' => true],
                [self::DEFAULT_ATTRIBUTE_CODE => array(), 'use_post_data_config' => []],
                false
            ),
            'required, empty, not use config case 2' => array(
                ['code' => self::DEFAULT_ATTRIBUTE_CODE, 'isRequired' => true, 'isValueEmpty' => true],
                [self::DEFAULT_ATTRIBUTE_CODE => array(), 'use_post_data_config' => ['config']],
                false
            ),
            'required, empty, use config' => array(
                ['code' => self::DEFAULT_ATTRIBUTE_CODE, 'isRequired' => true, 'isValueEmpty' => true],
                [self::DEFAULT_ATTRIBUTE_CODE => array(), 'use_post_data_config' => [self::DEFAULT_ATTRIBUTE_CODE]],
                true
            ),
        );
    }

    public function testValidateUnique()
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue('attribute_name'));
        $this->_attribute->expects($this->at(1))->method('__call')->with('getIsRequired');
        $this->_attribute->expects($this->at(2))->method('__call')->with('getIsUnique')->will($this->returnValue(true));

        $entityMock = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\AbstractEntity',
            array(),
            '',
            false,
            true,
            true,
            array('checkAttributeUniqueValue')
        );
        $this->_attribute->expects($this->any())->method('getEntity')->will($this->returnValue($entityMock));
        $entityMock->expects($this->at(0))->method('checkAttributeUniqueValue')->will($this->returnValue(true));
        $this->assertTrue($this->_model->validate(new \Magento\Framework\Object()));
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testValidateUniqueException()
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue('attribute_name'));
        $this->_attribute->expects($this->at(1))->method('__call')->with('getIsRequired');
        $this->_attribute->expects($this->at(2))->method('__call')->with('getIsUnique')->will($this->returnValue(true));

        $entityMock = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\AbstractEntity',
            array(),
            '',
            false,
            true,
            true,
            array('checkAttributeUniqueValue')
        );
        $frontMock = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend',
            array(),
            '',
            false,
            true,
            true,
            array('getLabel')
        );
        $this->_attribute->expects($this->any())->method('getEntity')->will($this->returnValue($entityMock));
        $this->_attribute->expects($this->any())->method('getFrontend')->will($this->returnValue($frontMock));
        $entityMock->expects($this->at(0))->method('checkAttributeUniqueValue')->will($this->returnValue(false));
        $this->assertTrue($this->_model->validate(new \Magento\Framework\Object()));
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
        $object = new \Magento\Framework\Object($data);
        $this->assertTrue($this->_model->validate($object));
    }

    public function validateDefaultSortDataProvider()
    {
        return array(
            array(
                'default_sort_by',
                [
                    'available_sort_by' => ['value1', 'value2'],
                    'default_sort_by' => 'value2',
                    'use_post_data_config' => array()
                ]
            ),
            array(
                'default_sort_by',
                [
                    'available_sort_by' => 'value1,value2',
                    'use_post_data_config' => ['default_sort_by']
                ]
            ),
            array(
                'default_sort_by',
                [
                    'available_sort_by' => NULL,
                    'default_sort_by' => NULL,
                    'use_post_data_config' => ['available_sort_by', 'default_sort_by', 'filter_price_range']
                ]
            ),
        );
    }

    /**
     * @param $attributeCode
     * @param $data
     * @dataProvider validateDefaultSortException
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testValidateDefaultSortException($attributeCode, $data)
    {
        $this->_attribute->expects($this->any())->method('getName')->will($this->returnValue($attributeCode));
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue('another value'));
        $object = new \Magento\Framework\Object($data);
        $this->_model->validate($object);
    }

    public function validateDefaultSortException()
    {
        return array(
            array(
                'default_sort_by',
                [
                    'available_sort_by' => NULL,
                    'use_post_data_config' => ['default_sort_by']
                ]
            ),
            array(
                'default_sort_by',
                [
                    'available_sort_by' => NULL,
                    'use_post_data_config' => []
                ]
            ),
            array(
                'default_sort_by',
                [
                    'available_sort_by' => ['value1', 'value2'],
                    'default_sort_by' => 'another value',
                    'use_post_data_config' => array()
                ]
            ),
            array(
                'default_sort_by',
                [
                    'available_sort_by' => 'value1',
                    'use_post_data_config' => array()
                ]
            ),
        );
    }
}
