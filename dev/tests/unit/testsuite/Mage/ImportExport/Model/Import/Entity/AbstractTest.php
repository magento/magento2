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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_ImportExport_Model_Import_Entity_Abstract
 */
class Mage_ImportExport_Model_Import_Entity_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Abstract import entity model
     *
     * @var Mage_ImportExport_Model_Import_Entity_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    public function setUp()
    {
        parent::setUp();

        $this->_model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_Entity_Abstract', array(),
            '', false, true, true, array('_saveValidatedBunches')
        );
    }

    public function tearDown()
    {
        unset($this->_model);

        parent::tearDown();
    }

    /**
     * Create mock for data helper and push it to registry
     *
     * @return Mage_ImportExport_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createDataHelperMock()
    {
        /** @var $helper Mage_ImportExport_Helper_Data */
        $helper = $this->getMock('Mage_ImportExport_Helper_Data', array('__'), array(), '', false);
        $helper->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));
        $registryKey = '_helper/Mage_ImportExport_Helper_Data';
        if (Mage::registry($registryKey)) {
            Mage::unregister($registryKey);
        }
        Mage::register($registryKey, $helper);

        return $helper;
    }

    /**
     * Create source adapter mock and set it into model object which tested in this class
     *
     * @param array $columns value which will be returned by method getColNames()
     * @return Mage_ImportExport_Model_Import_SourceAbstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createSourceAdapterMock(array $columns)
    {
        /** @var $source Mage_ImportExport_Model_Import_SourceAbstract|PHPUnit_Framework_MockObject_MockObject */
        $source = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_SourceAbstract', array(), '', false,
            true, true, array('getColNames')
        );
        $source->expects($this->any())
            ->method('getColNames')
            ->will($this->returnValue($columns));
        $this->_model->setSource($source);

        return $source;
    }

    /**
     * Test for method validateData()
     *
     * @covers Mage_ImportExport_Model_Import_Entity_Abstract::validateData
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Columns number: "%s" have empty headers
     */
    public function testValidateDataEmptyColumnName()
    {
        $this->_createDataHelperMock();
        $this->_createSourceAdapterMock(array(''));
        $this->_model->validateData();
    }

    /**
     * Test for method validateData()
     *
     * @covers Mage_ImportExport_Model_Import_Entity_Abstract::validateData
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Columns number: "%s" have empty headers
     */
    public function testValidateDataColumnNameWithWhitespaces()
    {
        $this->_createDataHelperMock();
        $this->_createSourceAdapterMock(array('  '));
        $this->_model->validateData();
    }

    /**
     * Test for method validateData()
     *
     * @covers Mage_ImportExport_Model_Import_Entity_Abstract::validateData
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Column names: "%s" are invalid
     */
    public function testValidateDataAttributeNames()
    {
        $this->_createDataHelperMock();
        $this->_createSourceAdapterMock(array('_test1'));
        $this->_model->validateData();
    }

    /**
     * Test for method isAttributeValid()
     *
     * @dataProvider isAttributeValidDataProvider
     * @covers Mage_ImportExport_Model_Import_Entity_Abstract::isAttributeValid
     *
     * @param string $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @param int $rowNum
     * @param bool $expectedResult
     */
    public function testIsAttributeValid($attrCode, array $attrParams, array $rowData, $rowNum, $expectedResult)
    {
        $this->_createDataHelperMock();
        $this->_createSourceAdapterMock(array('_test1'));
        $this->assertEquals($expectedResult,
            $this->_model->isAttributeValid($attrCode, $attrParams, $rowData, $rowNum));
    }

    /**
     * Data provider for testIsAttributeValid
     *
     * @return array
     */
    public function isAttributeValidDataProvider()
    {
        return array(
            array('created_at', array('type' => 'datetime'), array('created_at' => '2012-02-29'), 1, true),
            array('dob', array('type' => 'datetime'), array('dob' => '29.02.2012'), 1, true),
            array('created_at', array('type' => 'datetime'), array('created_at' => '02/29/2012'), 1, true),
            array('dob', array('type' => 'datetime'), array('dob' => '2012-02-29 21:12:59'), 1, true),
            array('created_at', array('type' => 'datetime'), array('created_at' => '29.02.2012 11:12:59'), 1, true),
            array('dob', array('type' => 'datetime'), array('dob' => '02/29/2012 11:12:59'), 1, true),
            array('created_at', array('type' => 'datetime'), array('created_at' => '2012602-29'), 1, false),
            array('dob', array('type' => 'datetime'), array('dob' => '32.12.2012'), 1, false),
            array('created_at', array('type' => 'datetime'), array('created_at' => '02/30/-2012'), 1, false),
            array('dob', array('type' => 'datetime'), array('dob' => '2012-13-29 21:12:59'), 1, false),
            array('created_at', array('type' => 'datetime'), array('created_at' => '11.02.4 11:12:59'), 1, false),
            array('dob', array('type' => 'datetime'), array('dob' => '02/29/2012 11:12:67'), 1, false)

        );
    }

}
