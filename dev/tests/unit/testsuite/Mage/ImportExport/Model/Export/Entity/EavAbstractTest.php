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
 * @category    Magento
 * @package     Mage_ImportExport
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_ImportExport_Model_Export_Entity_EavAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Abstract eav export model
     *
     * @var Mage_ImportExport_Model_Export_Entity_EavAbstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Attribute codes for tests
     *
     * @var array
     */
    protected $_expectedAttributes = array('firstname', 'lastname');

    public function setUp()
    {
        $this->_model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Export_Entity_EavAbstract', array(),
            '', false, true, true, array('_getExportAttributeCodes', 'getAttributeCollection', 'getAttributeOptions'));

        $this->_model->expects($this->once())
            ->method('_getExportAttributeCodes')
            ->will($this->returnValue($this->_expectedAttributes));
    }

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Test for method _addAttributesToCollection()
     *
     * @covers Mage_ImportExport_Model_Export_Entity_EavAbstract::_addAttributesToCollection
     */
    public function testAddAttributesToCollection()
    {
        $method = new ReflectionMethod($this->_model, '_addAttributesToCollection');
        $method->setAccessible(true);
        $stubCollection = new Stub_ImportExport_Model_Export_Entity_Eav_Collection();
        $stubCollection = $method->invoke($this->_model, $stubCollection);

        $this->assertEquals($this->_expectedAttributes, $stubCollection->getSelectedAttributes());
    }

    /**
     * Test for methods _addAttributeValuesToRow()
     *
     * @covers Mage_ImportExport_Model_Export_Entity_EavAbstract::_initAttrValues
     * @covers Mage_ImportExport_Model_Export_Entity_EavAbstract::_addAttributeValuesToRow
     */
    public function testAddAttributeValuesToRow()
    {
        $testAttributeCode = 'lastname';
        $testAttributeValue = 'value';
        $testAttributeOptions = array('value' => 'option');
        /** @var $testAttribute Mage_Eav_Model_Entity_Attribute */
        $testAttribute = $this->getMockForAbstractClass('Mage_Eav_Model_Entity_Attribute_Abstract', array(), '', false);
        $testAttribute->setAttributeCode($testAttributeCode);

        $this->_model->expects($this->any())
            ->method('getAttributeCollection')
            ->will($this->returnValue(array($testAttribute)));

        $this->_model->expects($this->any())
            ->method('getAttributeOptions')
            ->will($this->returnValue($testAttributeOptions));

        /** @var $item Mage_Core_Model_Abstract|PHPUnit_Framework_MockObject_MockObject */
        $item = $this->getMockForAbstractClass('Mage_Core_Model_Abstract', array(), '', false, true, true,
            array('getData'));
        $item->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($testAttributeValue));

        $method = new ReflectionMethod($this->_model, '_initAttributeValues');
        $method->setAccessible(true);
        $method->invoke($this->_model);

        $method = new ReflectionMethod($this->_model, '_addAttributeValuesToRow');
        $method->setAccessible(true);
        $row = $method->invoke($this->_model, $item);
        /**
         *  Prepare expected data
         */
        $expected = array();
        foreach ($this->_expectedAttributes as $code) {
            $expected[$code] = $testAttributeValue;
            if ($code == $testAttributeCode) {
                $expected[$code] = $testAttributeOptions[$expected[$code]];
            }
        }

        $this->assertEquals($expected, $row, 'Attributes were not added to result row');
    }
}
/**
 * Stub class which used for test which check list of attributes which will be fetched from DB
 */
class Stub_ImportExport_Model_Export_Entity_Eav_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
    /**
     * Selected attribute(s)
     *
     * @var array|int|Mage_Core_Model_Config_Element|string
     */
    protected $_selectedAttributes;

    /**
     * Join type
     *
     * @var string
     */
    protected $_joinType;

    public function __construct()
    {
    }

    /**
     * Stub method which save selected attribute(s) into private variable
     *
     * @param array|int|Mage_Core_Model_Config_Element|string $attribute
     * @param bool $joinType
     * @return Stub_ImportExport_Model_Export_Entity_Eav_Collection
     */
    public function addAttributeToSelect($attribute, $joinType = false)
    {
        $this->_selectedAttributes = $attribute;
        $this->_joinType = $joinType;
        return $this;
    }

    /**
     * Retrieve selected attribute(s)
     *
     * @return array|int|Mage_Core_Model_Config_Element|string
     */
    public function getSelectedAttributes()
    {
        return $this->_selectedAttributes;
    }
}
