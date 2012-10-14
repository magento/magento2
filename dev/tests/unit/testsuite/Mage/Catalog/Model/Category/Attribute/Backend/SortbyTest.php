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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Model_Category_Attribute_Backend_SortbyTest extends PHPUnit_Framework_TestCase
{
    const DEFAULT_ATTRIBUTE_CODE = 'attribute_name';

    /**
     * @var Mage_Catalog_Model_Category_Attribute_Backend_Sortby
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Category_Attribute_Backend_Sortby();
        $attribute = $this->getMockForAbstractClass('Mage_Eav_Model_Entity_Attribute_Abstract',
            array(), '', false, true, true, array('getName')
        );
        $attribute->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::DEFAULT_ATTRIBUTE_CODE));
        $this->_model->setAttribute($attribute);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @param $data
     * @param $expected
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave($data, $expected)
    {
        $object = new Varien_Object($data);
        $this->_model->beforeSave($object);
        $this->assertTrue($object->hasData(self::DEFAULT_ATTRIBUTE_CODE));
        $this->assertSame($expected, $object->getData(self::DEFAULT_ATTRIBUTE_CODE));
    }

    public function beforeSaveDataProvider()
    {
        return array(
            'attribute with specified value' => array(
                array(self::DEFAULT_ATTRIBUTE_CODE => 'test_value'),
                'test_value',
            ),
            'attribute with default value' => array(
                array(self::DEFAULT_ATTRIBUTE_CODE => null),
                null,
            ),
            'attribute does not exist' => array(
                array(),
                false,
            ),
        );
    }
}
