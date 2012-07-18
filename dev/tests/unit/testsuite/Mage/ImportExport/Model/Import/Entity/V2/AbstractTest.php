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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_ImportExport_Model_Import_Entity_V2_Abstract
 */
class Mage_ImportExport_Model_Import_Entity_V2_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Abstract import entity model
     *
     * @var Mage_ImportExport_Model_Import_Entity_V2_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    public function setUp()
    {
        parent::setUp();

        $this->_model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_Entity_V2_Abstract', array(),
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
     * Test for method _prepareRowForDb()
     */
    public function testPrepareRowForDb()
    {
        $expected = array(
            'test1' => 100,
            'test2' => null,
            'test3' => '',
            'test4' => 0,
            'test5' => 'test',
            'test6' => array(1, 2, 3),
            'test7' => array()
        );

        $method = new ReflectionMethod($this->_model, '_prepareRowForDb');
        $method->setAccessible(true);
        $actual = $method->invoke($this->_model, $expected);

        $expected['test3'] = null;
        $this->assertSame($expected, $actual);
    }

    /**
     * Test for method addRowError()
     */
    public function testAddRowError()
    {
        $this->_createDataHelperMock();

        $errorCode = 'error_code ';
        $errorColumnName = 'error_column';
        $this->_model->addRowError($errorCode . '%s', 0, $errorColumnName);

        $this->assertGreaterThan(0, $this->_model->getErrorsCount());

        $errors = $this->_model->getErrorMessages();
        $this->assertArrayHasKey($errorCode . $errorColumnName, $errors);
    }

    /**
     * Test for method importData()
     */
    public function testImportData()
    {
        $this->_model->expects($this->once())
            ->method('_importData');
        $this->_model->importData();
    }

    /**
     * Test for method isAttributeParticular()
     */
    public function testIsAttributeParticular()
    {
        $attributeCode = 'test';

        $property = new ReflectionProperty($this->_model, '_particularAttributes');
        $property->setAccessible(true);
        $property->setValue($this->_model, array($attributeCode));

        $this->assertTrue($this->_model->isAttributeParticular($attributeCode));
    }

    /**
     * Test for method _addMessageTemplate()
     */
    public function testAddMessageTemplate()
    {
        $this->_createDataHelperMock();

        $errorCode = 'test';
        $message = 'This is test error message';
        $this->_model->addMessageTemplate($errorCode, $message);

        $this->_model->addRowError($errorCode, 0);
        $errors = $this->_model->getErrorMessages();

        $this->assertArrayHasKey($message, $errors);
    }

    /**
     * Test for method isDataValid()
     */
    public function testIsDataValid()
    {
        /** @var $model Mage_ImportExport_Model_Import_Entity_V2_Abstract|PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_Entity_V2_Abstract', array(), '', false,
            true, true, array('validateData'));
        $model->expects($this->any())
            ->method('validateData');
        $this->assertTrue($model->isDataValid());
        $model->addRowError('test', 1);
        $this->assertFalse($model->isDataValid());
    }

    /**
     * Test for method isRowAllowedToImport()
     */
    public function testIsRowAllowedToImport()
    {
        $rows = 4;
        $skippedRows = array(
            2 => true,
            4 => true
        );
        $property = new ReflectionProperty($this->_model, '_skippedRows');
        $property->setAccessible(true);
        $property->setValue($this->_model, $skippedRows);

        for ($i = 1; $i <= $rows; $i++) {
            $this->assertFalse($this->_model->isRowAllowedToImport(array(), $i));
        }

        $this->_model->expects($this->any())
            ->method('validateRow')
            ->will($this->returnValue(true));

        for ($i = 1; $i <= $rows; $i++) {
            $expected = true;
            if (isset($skippedRows[$i])) {
                $expected = !$skippedRows[$i];
            }
            $this->assertSame($expected, $this->_model->isRowAllowedToImport(array(), $i));
        }
    }

    /**
     * Test for method getBehavior()
     */
    public function testGetBehavior()
    {
        $behaviors = array(
            Mage_ImportExport_Model_Import::BEHAVIOR_APPEND,
            Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE,
            Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
        );

        $property = new ReflectionProperty($this->_model, '_availableBehaviors');
        $property->setAccessible(true);
        $property->setValue($this->_model, $behaviors);

        $default = Mage_ImportExport_Model_Import::getDefaultBehavior();

        foreach ($behaviors as $behavior) {
            $this->_model->setParameters(array(
                'behavior' => $behavior
            ));
            $this->assertSame($behavior, $this->_model->getBehavior());
        }

        $this->_model->setParameters(array(
            'behavior' => 'custom'
        ));
        $this->assertSame($default, $this->_model->getBehavior());
    }

    /**
     * Test for method isAttributeValid()
     *
     * @param array $data
     * @dataProvider attributeList
     */
    public function testIsAttributeValid(array $data)
    {
        $registryKey = '_helper/Mage_Core_Helper_String';
        if (!Mage::registry($registryKey)) {
            $helper = new Mage_Core_Helper_String();
            Mage::register($registryKey, $helper);
        }

        $attributeCode = $data['code'];
        $attributeParams = array(
            'type'      => $data['type'],
            'options'   => isset($data['options']) ? $data['options'] : null,
            'is_unique' => isset($data['is_unique']) ? $data['is_unique'] : null
        );

        $rowData = array(
            $attributeCode => $data['valid_value']
        );
        $this->assertTrue($this->_model->isAttributeValid($attributeCode, $attributeParams, $rowData, 0));

        $rowData[$attributeCode] = $data['invalid_value'];
        $this->assertFalse($this->_model->isAttributeValid($attributeCode, $attributeParams, $rowData, 0));

        $this->assertEquals(1, $this->_model->getErrorsCount(), 'Wrong count of errors');
    }

    /**
     * Data provide which retrieve data for test attributes
     *
     * @static
     * @return array
     */
    public static function attributeList()
    {
        $longString = str_pad('', Mage_ImportExport_Model_Import_Entity_V2_Abstract::DB_MAX_TEXT_LENGTH, 'x');

        return array(
            array(
                array(
                    'code'          => 'test1',
                    'type'          => 'decimal',
                    'valid_value'   => 1.5,
                    'invalid_value' => 'test'
                )
            ),
            array(
                array(
                    'code'          => 'test2',
                    'type'          => 'varchar',
                    'valid_value'   => 'test string',
                    'invalid_value' => substr($longString, 0,
                        Mage_ImportExport_Model_Import_Entity_V2_Abstract::DB_MAX_VARCHAR_LENGTH
                    )
                )
            ),
            array(
                array(
                    'code'          => 'test3',
                    'type'          => 'select',
                    'valid_value'   => 'test2',
                    'invalid_value' => 'custom',
                    'options'       => array(
                        'test1' => 1,
                        'test2' => 2,
                        'test3' => 3
                    )
                )
            ),
            array(
                array(
                    'code'          => 'test4',
                    'type'          => 'multiselect',
                    'valid_value'   => 'test2',
                    'invalid_value' => 'custom',
                    'options'       => array(
                        'test1' => 1,
                        'test2' => 2,
                        'test3' => 3
                    )
                )
            ),
            array(
                array(
                    'code'          => 'test5',
                    'type'          => 'int',
                    'valid_value'   => 100,
                    'invalid_value' => 'custom'
                )
            ),
            array(
                array(
                    'code'          => 'test6',
                    'type'          => 'datetime',
                    'valid_value'   => '2012-06-15 15:50',
                    'invalid_value' => '2012-30-30'
                )
            ),
            array(
                array(
                    'code'          => 'test7',
                    'type'          => 'text',
                    'valid_value'   => 'test string',
                    'invalid_value' => $longString
                )
            ),
            array(
                array(
                    'code'          => 'test8',
                    'type'          => 'int',
                    'is_unique'     => true,
                    'valid_value'   => 1,
                    'invalid_value' => 1
                )
            )
        );
    }

    /**
     * Test for method validateData()
     *
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Abstract::validateData()
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Can not find required columns: %s
     */
    public function testValidateDataPermanentAttributes()
    {
        $this->_createDataHelperMock();

        $columns = array('test1', 'test2');
        $this->_createSourceAdapterMock($columns);

        $permanentAttributes = array('test2', 'test3');
        $property = new ReflectionProperty($this->_model, '_permanentAttributes');
        $property->setAccessible(true);
        $property->setValue($this->_model, $permanentAttributes);

        $this->_model->validateData();
    }

    /**
     * Test for method validateData()
     *
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Abstract::validateData()
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
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Abstract::validateData()
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
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Abstract::validateData()
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
     * Create source adapter mock and set it into model object which tested in this class
     *
     * @param array $columns value which will be returned by method getColNames()
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createSourceAdapterMock(array $columns)
    {
        /** @var $source Mage_ImportExport_Model_Import_Adapter_Abstract|PHPUnit_Framework_MockObject_MockObject */
        $source = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_Adapter_Abstract', array(), '', false,
            true, true, array('getColNames')
        );
        $source->expects($this->any())
            ->method('getColNames')
            ->will($this->returnValue($columns));
        $this->_model->setSource($source);

        return $source;
    }

    /**
     * Test entity subtype getter
     */
    public function testGetEntitySubtype()
    {
        $this->assertNull($this->_model->getEntitySubtype());

        $entitySubtype = 'customers';
        $this->_model->setParameters(array('entity_subtype' => $entitySubtype));

        $this->assertEquals($entitySubtype, $this->_model->getEntitySubtype());
    }
}
