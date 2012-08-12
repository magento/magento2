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
 * Test class for Mage_ImportExport_Model_Import_EntityAbstract
 *
 * @todo Fix tests in the scope of https://wiki.magento.com/display/MAGE2/Technical+Debt+%28Team-Donetsk-B%29
 */
class Mage_ImportExport_Model_Import_EntityAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Abstract import entity model
     *
     * @var Mage_ImportExport_Model_Import_EntityAbstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * List of available behaviors
     *
     * @var array
     */
    protected $_availableBehaviors = array(
        Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
        Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
        Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
    );

    public function setUp()
    {
        $this->_model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_EntityAbstract',
            array($this->_getModelDependencies())
        );
    }

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Create mocks for all $this->_model dependencies
     *
     * @return array
     */
    protected function _getModelDependencies()
    {
        $mageHelper = $this->getMock('Mage_ImportExport_Helper_Data', array('__'));
        $mageHelper->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));

        $data = array(
            'data_source_model'            => 'not_used',
            'connection'                   => 'not_used',
            'helpers'                      => array('Mage_ImportExport_Helper_Data' => $mageHelper),
            'json_helper'                  => 'not_used',
            'string_helper'                => new Mage_Core_Helper_String(),
            'page_size'                    => 1,
            'max_data_size'                => 1,
            'bunch_size'                   => 1,
            'collection_by_pages_iterator' => 'not_used',
        );

        return $data;
    }

    /**
     * Test for method _prepareRowForDb()
     *
     * @covers Mage_ImportExport_Model_Import_EntityAbstract::_prepareRowForDb
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
        $errorCode       = 'error_code ';
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

        $property = new ReflectionProperty($this->_model, '_specialAttributes');
        $property->setAccessible(true);
        $property->setValue($this->_model, array($attributeCode));

        $this->assertTrue($this->_model->isAttributeParticular($attributeCode));
    }

    /**
     * Test for method _addMessageTemplate()
     */
    public function testAddMessageTemplate()
    {
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
        /** @var $model Mage_ImportExport_Model_Import_EntityAbstract|PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_EntityAbstract', array(), '', false,
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

        $modelForValidateRow = clone $this->_model;
        $modelForValidateRow->expects($this->any())
            ->method('validateRow')
            ->will($this->returnValue(false));

        for ($i = 1; $i <= $rows; $i++) {
            $this->assertFalse($modelForValidateRow->isRowAllowedToImport(array(), $i));
        }

        $modelForIsAllowed = clone $this->_model;
        $modelForIsAllowed->expects($this->any())
            ->method('validateRow')
            ->will($this->returnValue(true));

        for ($i = 1; $i <= $rows; $i++) {
            $expected = true;
            if (isset($skippedRows[$i])) {
                $expected = !$skippedRows[$i];
            }
            $this->assertSame($expected, $modelForIsAllowed->isRowAllowedToImport(array(), $i));
        }
    }

    /**
     * Test for method getBehavior() with $rowData argument = null
     *
     * @covers Mage_ImportExport_Model_Import_EntityAbstract::getBehavior
     */
    public function testGetBehaviorWithoutRowData()
    {
        $property = new ReflectionProperty($this->_model, '_availableBehaviors');
        $property->setAccessible(true);
        $property->setValue($this->_model, $this->_availableBehaviors);

        $default = Mage_ImportExport_Model_Import_EntityAbstract::getDefaultBehavior();

        foreach ($this->_availableBehaviors as $behavior) {
            $this->_model->setParameters(array(
                'behavior' => $behavior
            ));
            $this->assertSame($behavior, $this->_model->getBehavior());
        }

        $this->_model->setParameters(array(
            'behavior' => 'incorrect_string'
        ));
        $this->assertSame($default, $this->_model->getBehavior());
    }

    /**
     * Different cases to cover all code parts in Mage_ImportExport_Model_Import_EntityAbstract::getBehavior()
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function dataProviderForTestGetBehaviorWithRowData()
    {
        return array(
            "add/update behavior and row with delete in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION =>
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION_VALUE_DELETE
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
            ),
            "delete behavior and row with delete in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION =>
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION_VALUE_DELETE
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
            ),
            "custom behavior and row with delete in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION =>
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION_VALUE_DELETE
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
            ),
            "add/update behavior and row with update in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => 'update'
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
            ),
            "delete behavior and row with update in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => 'update'
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
            ),
            "custom behavior and row with update in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => 'update'
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
            ),
            "add/update behavior and row with bogus string in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => microtime(true)
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
            ),
            "delete behavior and row with bogus string in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => microtime(true)
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
            ),
            "custom behavior and row with bogus string in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => microtime(true)
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
            ),
            "add/update behavior and row with null in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => null
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
            ),
            "delete behavior and row with null in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => null
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
            ),
            "custom behavior and row with null in action column" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'          => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => null
                ),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
            ),
            "add/update behavior and empty row" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$rowData'          => null,
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
            ),
            "delete behavior and empty row" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
                '$rowData'          => null,
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
            ),
            "custom behavior and empty row" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'          => null,
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM
            ),
            "add/update behavior and row is empty array" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$rowData'          => array(),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
            ),
            "delete behavior and empty row is empty array" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
                '$rowData'          => array(),
                '$expectedBehavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
            ),
            "custom behavior and empty row is empty array" => array(
                '$inputBehavior'    => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'          => array(),
                '$expectedBehavior' => Mage_ImportExport_Model_Import_EntityAbstract::getDefaultBehavior()
            ),
            "custom behavior and row with delete in action column and empty available behaviors" => array(
                '$inputBehavior'      => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'            => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION =>
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION_VALUE_DELETE
                ),
                '$expectedBehavior'   => Mage_ImportExport_Model_Import_EntityAbstract::getDefaultBehavior(),
                '$availableBehaviors' => array()
            ),
            "custom behavior and row with update in action column and empty available behaviors" => array(
                '$inputBehavior'      => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'            => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => 'update'
                ),
                '$expectedBehavior'   => Mage_ImportExport_Model_Import_EntityAbstract::getDefaultBehavior(),
                '$availableBehaviors' => array()
            ),
            "custom behavior and row with bogus string in action column and empty available behaviors" => array(
                '$inputBehavior'      => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'            => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => microtime(true)
                ),
                '$expectedBehavior'   => Mage_ImportExport_Model_Import_EntityAbstract::getDefaultBehavior(),
                '$availableBehaviors' => array()
            ),
            "custom behavior and row with null in action column and empty available behaviors" => array(
                '$inputBehavior'      => Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
                '$rowData'            => array(
                    Mage_ImportExport_Model_Import_EntityAbstract::COLUMN_ACTION => null
                ),
                '$expectedBehavior'   => Mage_ImportExport_Model_Import_EntityAbstract::getDefaultBehavior(),
                '$availableBehaviors' => array()
            ),
        );
    }

    /**
     * Test for method getBehavior() with $rowData argument = null
     *
     * @covers Mage_ImportExport_Model_Import_EntityAbstract::getBehavior
     *
     * @dataProvider dataProviderForTestGetBehaviorWithRowData
     * @param $inputBehavior
     * @param $rowData
     * @param $expectedBehavior
     * @param null $availableBehaviors
     */
    public function testGetBehaviorWithRowData($inputBehavior, $rowData, $expectedBehavior, $availableBehaviors = null)
    {
        $property = new ReflectionProperty($this->_model, '_availableBehaviors');
        $property->setAccessible(true);

        if (isset($availableBehaviors)) {
            $property->setValue($this->_model, $availableBehaviors);
        } else {
            $property->setValue($this->_model, $this->_availableBehaviors);
        }
        $this->_model->setParameters(array('behavior' => $inputBehavior));
        $this->assertSame($expectedBehavior, $this->_model->getBehavior($rowData));
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
        $longString = str_pad('', Mage_ImportExport_Model_Import_EntityAbstract::DB_MAX_TEXT_LENGTH, 'x');

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
                        Mage_ImportExport_Model_Import_EntityAbstract::DB_MAX_VARCHAR_LENGTH
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
     * @covers Mage_ImportExport_Model_Import_EntityAbstract::validateData
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Can not find required columns: %s
     */
    public function testValidateDataPermanentAttributes()
    {
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
     * @covers Mage_ImportExport_Model_Import_EntityAbstract::validateData
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Columns number: "%s" have empty headers
     */
    public function testValidateDataEmptyColumnName()
    {
        $this->_createSourceAdapterMock(array(''));
        $this->_model->validateData();
    }

    /**
     * Test for method validateData()
     *
     * @covers Mage_ImportExport_Model_Import_EntityAbstract::validateData
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Columns number: "%s" have empty headers
     */
    public function testValidateDataColumnNameWithWhitespaces()
    {
        $this->_createSourceAdapterMock(array('  '));
        $this->_model->validateData();
    }

    /**
     * Test for method validateData()
     *
     * @covers Mage_ImportExport_Model_Import_EntityAbstract::validateData
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Column names: "%s" are invalid
     */
    public function testValidateDataAttributeNames()
    {
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
}
