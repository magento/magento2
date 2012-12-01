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

class Mage_ImportExport_Model_Import_Entity_CustomerCompositeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_ImportExport_Model_Import_Entity_CustomerComposite
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_customerAttributes = array('firstname', 'lastname', 'dob');

    /**
     * @var array
     */
    protected $_addressAttributes = array('city', 'country', 'street');

    /**
     * List of mocked methods for customer and address entity adapters
     *
     * @var array
     */
    protected $_entityMockedMethods = array(
        'validateRow',
        'getErrorMessages',
        'getErrorsCount',
        'getErrorsLimit',
        'getInvalidRowsCount',
        'getNotices',
        'getProcessedEntitiesCount',
        'setParameters',
        'setSource',
        'importData',
    );

    /**
     * Expected prepared data after method Mage_ImportExport_Model_Import_Entity_CustomerComposite::_prepareRowForDb
     *
     * @var array
     */
    protected $_preparedData = array(
        '_scope' => Mage_ImportExport_Model_Import_Entity_CustomerComposite::SCOPE_DEFAULT,
        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_WEBSITE    => 'admin',
        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_EMAIL      => 'test@qwewqeq.com',
        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => null,
    );

    /**
     * @return Mage_ImportExport_Model_Import_Entity_CustomerComposite
     */
    protected function _getModelMock()
    {
        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $this->_getCustomerEntityMock();
        $data['address_entity']  = $this->_getAddressEntityMock();
        $this->_model = new Mage_ImportExport_Model_Import_Entity_CustomerComposite($data);

        return $this->_model;
    }

    /**
     * Returns entity mock for method testPrepareRowForDb
     *
     * @return Mage_ImportExport_Model_Import_Entity_CustomerComposite
     */
    protected function _getModelMockForPrepareRowForDb()
    {
        $customerEntity = $this->_getCustomerEntityMock(array('validateRow'));
        $customerEntity->expects($this->any())
            ->method('validateRow')
            ->will($this->returnValue(true));

        $customerStorage = $this->getMock('stdClass', array('getCustomerId'));
        $customerStorage->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $addressEntity = $this->_getAddressEntityMock(array('validateRow', 'getCustomerStorage'));
        $addressEntity->expects($this->any())
            ->method('validateRow')
            ->will($this->returnValue(true));
        $addressEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->will($this->returnValue($customerStorage));

        $dataSourceMock = $this->getMock('stdClass', array('cleanBunches', 'saveBunch'));
        $dataSourceMock->expects($this->once())
            ->method('saveBunch')
            ->will($this->returnCallback(array($this, 'verifyPrepareRowForDbData')));

        $jsonHelper = $this->getMock('stdClass', array('jsonEncode'));

        $data = $this->_getModelDependencies();
        $data['customer_entity']   = $customerEntity;
        $data['address_entity']    = $addressEntity;
        $data['data_source_model'] = $dataSourceMock;
        $data['json_helper']       = $jsonHelper;
        $this->_model = new Mage_ImportExport_Model_Import_Entity_CustomerComposite($data);

        return $this->_model;
    }

    /**
     * Returns entity mock for method testImportData
     *
     * @param bool $isDeleteBehavior
     * @param boolean $customerImport
     * @param boolean $addressImport
     * @return Mage_ImportExport_Model_Import_Entity_CustomerComposite
     */
    protected function _getModelMockForImportData($isDeleteBehavior, $customerImport, $addressImport)
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $customerEntity->expects($this->once())
            ->method('importData')
            ->will($this->returnValue($customerImport));

        $addressEntity = $this->_getAddressEntityMock();
        // address import starts only if customer import finished successfully
        if ($isDeleteBehavior || !$customerImport) {
            $addressEntity->expects($this->never())
                ->method('importData');
        } else {
            $addressEntity->expects($this->once())
                ->method('importData')
                ->will($this->returnValue($addressImport));
        }

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity']  = $addressEntity;
        $this->_model = new Mage_ImportExport_Model_Import_Entity_CustomerComposite($data);

        return $this->_model;
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @param array $mockedMethods
     * @return Mage_ImportExport_Model_Import_Entity_Eav_Customer|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCustomerEntityMock(array $mockedMethods = null)
    {
        if (is_null($mockedMethods)) {
            $mockedMethods = $this->_entityMockedMethods;
        }
        $mockedMethods[] = 'getAttributeCollection';
        $mockedMethods[] = 'getWebsiteId';

        /** @var $customerEntity Mage_ImportExport_Model_Import_Entity_Eav_Customer */
        $customerEntity = $this->getMock('Mage_ImportExport_Model_Import_Entity_Eav_Customer', $mockedMethods, array(),
            '', false
        );

        $attributeList = array();
        foreach ($this->_customerAttributes as $code) {
            $attribute = new Varien_Object(array(
                'attribute_code' => $code
            ));
            $attributeList[] = $attribute;
        }
        $customerEntity->expects($this->once())
            ->method('getAttributeCollection')
            ->will($this->returnValue($attributeList));

        return $customerEntity;
    }

    /**
     * @param array $mockedMethods
     * @return Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getAddressEntityMock(array $mockedMethods = null)
    {
        if (is_null($mockedMethods)) {
            $mockedMethods = $this->_entityMockedMethods;
        }
        $mockedMethods[] = 'getAttributeCollection';

        /** @var $addressEntity Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address */
        $addressEntity = $this->getMock('Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address', $mockedMethods,
            array(), '', false
        );

        $attributeList = array();
        foreach ($this->_addressAttributes as $code) {
            $attribute = new Varien_Object(array(
                'attribute_code' => $code
            ));
            $attributeList[] = $attribute;
        }
        $addressEntity->expects($this->once())
            ->method('getAttributeCollection')
            ->will($this->returnValue($attributeList));

        return $addressEntity;
    }

    /**
     * Retrieve all necessary objects mocks which used inside customer storage
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
            'customer_data_source_model'   => 'not_used',
            'address_data_source_model'    => 'not_used',
            'connection'                   => 'not_used',
            'helpers'                      => array('Mage_ImportExport_Helper_Data' => $mageHelper),
            'json_helper'                  => 'not_used',
            'string_helper'                => new Mage_Core_Helper_String(),
            'page_size'                    => 1,
            'max_data_size'                => 1,
            'bunch_size'                   => 1,
            'collection_by_pages_iterator' => 'not_used',
            'next_customer_id'             => 1
        );

        return $data;
    }

    /**
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::isAttributeParticular
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::_initAddressAttributes
     */
    public function testIsAttributeParticular()
    {
        $this->_getModelMock();
        foreach ($this->_addressAttributes as $code) {
            $this->assertTrue(
                $this->_model->isAttributeParticular(
                    Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_ADDRESS_PREFIX . $code
                ),
                'Attribute must be particular'
            );
        }
        $this->assertFalse($this->_model->isAttributeParticular('test'), 'Attribute must not be particular');
    }

    /**
     * @dataProvider getRowDataProvider
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::validateRow
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::_getRowScope
     *
     * @param array $rows
     * @param array $calls
     * @param bool $validationReturn
     * @param array $expectedErrors
     * @param int $behavior
     */
    public function testValidateRow(array $rows, array $calls, $validationReturn, array $expectedErrors, $behavior)
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $this->_entityMockedMethods[] = 'getCustomerStorage';
        $addressEntity  = $this->_getAddressEntityMock();

        $customerEntity->expects($this->exactly($calls['customerValidationCalls']))
            ->method('validateRow')
            ->will($this->returnValue($validationReturn));

        $customerEntity->expects($this->any())
            ->method('getErrorMessages')
            ->will($this->returnValue(array()));

        $addressEntity->expects($this->exactly($calls['addressValidationCalls']))
            ->method('validateRow')
            ->will($this->returnValue($validationReturn));

        $customerStorage = $this->getMock('stdClass', array('getCustomerId'));
        $customerStorage->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(true));
        $addressEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->will($this->returnValue($customerStorage));

        $addressEntity->expects($this->any())
            ->method('getErrorMessages')
            ->will($this->returnValue(array()));


        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity']  = $addressEntity;
        $this->_model = new Mage_ImportExport_Model_Import_Entity_CustomerComposite($data);
        $this->_model->setParameters(array('behavior' => $behavior));

        foreach ($rows as $index => $data) {
            $this->_model->validateRow($data, $index);
        }
        foreach ($expectedErrors as $error) {
            $this->assertArrayHasKey($error, $this->_model->getErrorMessages());
        }
    }

    /**
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::_prepareAddressRowData
     */
    public function testPrepareAddressRowData()
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $this->_entityMockedMethods[] = 'getCustomerStorage';
        $addressEntity  = $this->_getAddressEntityMock();

        $customerEntity->expects($this->once())
            ->method('validateRow')
            ->will($this->returnValue(true));

        $addressEntity->expects($this->once())
            ->method('validateRow')
            ->will($this->returnCallback(array($this, 'validateAddressRowParams')));

        $customerStorage = $this->getMock('stdClass', array('getCustomerId'));
        $customerStorage->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(true));
        $addressEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->will($this->returnValue($customerStorage));

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity']  = $addressEntity;
        $this->_model = new Mage_ImportExport_Model_Import_Entity_CustomerComposite($data);

        $rowData = array(
            Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL                 => 'test@test.com',
            Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE               => 'admin',
            Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID    => null,
            Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_BILLING  => true,
            Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_SHIPPING => true,
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'dob'       => '1984-11-11',
        );

        $this->_model->validateRow($rowData, 1);
    }

    /**
     * @param array $rowData
     * @param int $rowNumber
     */
    public function validateAddressRowParams(array $rowData, $rowNumber)
    {
        foreach ($this->_customerAttributes as $attributeCode) {
            $this->assertArrayNotHasKey($attributeCode, $rowData);
        }
        $this->assertArrayHasKey(Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_BILLING,
            $rowData
        );
        $this->assertArrayHasKey(Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_SHIPPING,
            $rowData
        );
        $this->assertEquals(1, $rowNumber);
    }

    /**
     * @return array
     */
    public function getRowDataProvider()
    {
        return array(
            'customer and address rows, append behavior' => array(
                '$rows' => array(
                    array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL   => 'test@test.com',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE => 'admin',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => null
                    ),
                    array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL   => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => 1
                    )
                ),
                '$calls'            => array(
                    'customerValidationCalls' => 1,
                    'addressValidationCalls'  => 2
                ),
                '$validationReturn' => true,
                '$expectedErrors'   => array(),
                '$behavior'         => Mage_ImportExport_Model_Import::BEHAVIOR_APPEND
            ),
            'customer and address rows, delete behavior' => array(
                '$rows' => array(
                    array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL   => 'test@test.com',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE => 'admin',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => null
                    ),
                    array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL   => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => 1
                    )
                ),
                '$calls'            => array(
                    'customerValidationCalls' => 1,
                    'addressValidationCalls'  => 0
                ),
                '$validationReturn' => true,
                '$expectedErrors'   => array(),
                '$behavior'         => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
            ),
            'customer and two addresses row, append behavior' => array(
                '$rows' => array(
                    array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL => 'test@test.com',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE => 'admin',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => null
                    ),
                    array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => 1
                    ),
                    array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => 2
                    )
                ),
                '$calls'            => array(
                    'customerValidationCalls' => 1,
                    'addressValidationCalls'  => 3
                ),
                '$validationReturn' => true,
                '$expectedErrors'   => array(),
                '$behavior'         => Mage_ImportExport_Model_Import::BEHAVIOR_APPEND
            ),
            'customer and addresses row with filed validation, append behavior' => array(
                '$rows' => array(
                    array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL => 'test@test.com',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE => 'admin',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => null
                    ),
                    array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE => '',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => 1
                    )
                ),
                '$calls'            => array(
                    'customerValidationCalls' => 1,
                    'addressValidationCalls'  => 0
                ),
                '$validationReturn' => false,
                '$expectedErrors'   => array('Orphan rows that will be skipped due default row errors'),
                '$behavior'         => Mage_ImportExport_Model_Import::BEHAVIOR_APPEND
            )
        );
    }

    /**
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::setParameters
     */
    public function testSetParameters()
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $addressEntity  = $this->_getAddressEntityMock();

        $customerEntity->expects($this->once())
            ->method('setParameters')
            ->will($this->returnCallback(array($this, 'callbackCheckParameters')));
        $addressEntity->expects($this->once())
            ->method('setParameters')
            ->will($this->returnCallback(array($this, 'callbackCheckParameters')));
        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity']  = $addressEntity;
        $this->_model = new Mage_ImportExport_Model_Import_Entity_CustomerComposite($data);

        $params = array(
            'behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_APPEND
        );
        $this->_model->setParameters($params);
    }

    /**
     * @param array $params
     */
    public function callbackCheckParameters(array $params)
    {
        $this->assertArrayHasKey('behavior', $params);
        $this->assertEquals(Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE, $params['behavior']);
    }

    /**
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::setSource
     */
    public function testSetSource()
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $addressEntity  = $this->_getAddressEntityMock();

        $customerEntity->expects($this->once())
            ->method('setSource');
        $addressEntity->expects($this->once())
            ->method('setSource');
        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity']  = $addressEntity;
        $this->_model = new Mage_ImportExport_Model_Import_Entity_CustomerComposite($data);

        $source = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_SourceAbstract', array(), '', false);
        $this->_model->setSource($source);
    }

    /**
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::setErrorMessages
     */
    public function testGetErrorMessages()
    {
        $errorMessages = array(
            'Required field' => array(1,2,3),
            'Bad password'   => array(1),
            'Wrong website'  => array(1,2)
        );
        $customerEntity = $this->_getCustomerEntityMock();
        $customerEntity->expects($this->once())
            ->method('getErrorMessages')
            ->will($this->returnValue($errorMessages));

        $errorMessages = array(
            'Required field'   => array(2,3,4,5),
            'Wrong address'  => array(1,2)
        );
        $addressEntity = $this->_getAddressEntityMock();
        $addressEntity->expects($this->once())
            ->method('getErrorMessages')
            ->will($this->returnValue($errorMessages));

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity']  = $addressEntity;
        $this->_model = new Mage_ImportExport_Model_Import_Entity_CustomerComposite($data);

        $this->_model->addRowError('Bad password', 1);

        $expectedErrors = array(
            'Required field' => array(1,2,3,4,5),
            'Bad password'   => array(2),
            'Wrong website'  => array(1,2),
            'Wrong address'  => array(1,2)
        );

        $actualErrors = $this->_model->getErrorMessages();
        foreach ($expectedErrors as $error => $rows) {
            $this->assertArrayHasKey($error, $actualErrors);
            $this->assertSame($rows, array_values($actualErrors[$error]));
        }
    }

    /**
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::_prepareRowForDb
     */
    public function testPrepareRowForDb()
    {
        $this->_getModelMockForPrepareRowForDb();
        $pathToCsvFile = __DIR__ . '/_files/customer_composite_prepare_row_for_db.csv';
        $source = new Mage_ImportExport_Model_Import_Source_Csv($pathToCsvFile);
        $this->_model->setSource($source);
        $this->_model->validateData();  // assertions processed in self::verifyPrepareRowForDbData
    }

    /**
     * Callback for Mage_ImportExport_Model_Resource_Import_Data::saveBunch to verify correctness of data
     * for method Mage_ImportExport_Model_Import_Entity_CustomerComposite::_prepareRowForDb
     *
     * @param string $entityType
     * @param string $behavior
     * @param array $bunchRows
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function verifyPrepareRowForDbData($entityType, $behavior, $bunchRows)
    {
        // source data contains only one record
        $this->assertCount(1, $bunchRows);

        // array must has all expected data
        $customerData = $bunchRows[0];
        foreach ($this->_preparedData as $expectedKey => $expectedValue) {
            $this->assertArrayHasKey($expectedKey, $customerData);
            $this->assertEquals($expectedValue, $customerData[$expectedKey]);
        }
    }

    /**
     * Data provider for method testImportData
     *
     * @return array
     */
    public function dataProviderTestImportData()
    {
        return array(
            'add_update_behavior_customer_true_address_true' => array(
                '$behavior'       => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => true,
                '$addressImport'  => true,
                '$result'         => true,
            ),
            'add_update_behavior_customer_true_address_false' => array(
                '$behavior'       => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => true,
                '$addressImport'  => false,
                '$result'         => false,
            ),
            'add_update_behavior_customer_false_address_true' => array(
                '$behavior'       => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => false,
                '$addressImport'  => true,
                '$result'         => false,
            ),
            'add_update_behavior_customer_false_address_false' => array(
                '$behavior'       => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => false,
                '$addressImport'  => false,
                '$result'         => false,
            ),
            'delete_behavior_customer_true' => array(
                '$behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
                '$customerImport' => true,
                '$addressImport'  => false,
                '$result'         => true,
            ),
            'delete_behavior_customer_false' => array(
                '$behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
                '$customerImport' => false,
                '$addressImport'  => false,
                '$result'         => false,
            ),
        );
    }

    /**
     * @dataProvider dataProviderTestImportData
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::_importData
     *
     * @param string $behavior
     * @param boolean $customerImport
     * @param boolean $addressImport
     * @param boolean $result
     */
    public function testImportData($behavior, $customerImport, $addressImport, $result)
    {
        $isDeleteBehavior = $behavior == Mage_ImportExport_Model_Import::BEHAVIOR_DELETE;
        $entityMock = $this->_getModelMockForImportData($isDeleteBehavior, $customerImport, $addressImport);
        $entityMock->setParameters(array('behavior' => $behavior));
        $importResult = $entityMock->importData();
        if ($result) {
            $this->assertTrue($importResult);
        } else {
            $this->assertFalse($importResult);
        }
    }

    /**
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::getErrorsCount
     */
    public function testGetErrorsCount()
    {
        $customerReturnData = 1;
        $addressReturnData = 2;
        $model = $this->_getModelForGetterTest('getErrorsCount', $customerReturnData, $addressReturnData);
        $model->addRowError(Mage_ImportExport_Model_Import_Entity_CustomerComposite::ERROR_ROW_IS_ORPHAN, 1);

        $this->assertEquals($customerReturnData + $addressReturnData + 1, $model->getErrorsCount());
    }

    /**
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::getInvalidRowsCount
     */
    public function testGetInvalidRowsCount()
    {
        $customerReturnData = 3;
        $addressReturnData = 2;
        $model = $this->_getModelForGetterTest('getInvalidRowsCount', $customerReturnData, $addressReturnData);
        $model->addRowError(Mage_ImportExport_Model_Import_Entity_CustomerComposite::ERROR_ROW_IS_ORPHAN, 1);

        $this->assertEquals($customerReturnData + $addressReturnData + 1, $model->getInvalidRowsCount());
    }

    /**
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::getProcessedEntitiesCount
     */
    public function testGetProcessedEntitiesCount()
    {
        $customerReturnData = 3;
        $addressReturnData = 4;
        $model = $this->_getModelForGetterTest('getProcessedEntitiesCount', $customerReturnData, $addressReturnData);

        $this->assertEquals($customerReturnData + $addressReturnData, $model->getProcessedEntitiesCount());
    }

    /**
     * @param string $method
     * @param int $customerReturnData
     * @param int $addressReturnData
     * @return Mage_ImportExport_Model_Import_Entity_CustomerComposite
     */
    protected function _getModelForGetterTest($method, $customerReturnData, $addressReturnData)
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $addressEntity = $this->_getAddressEntityMock();

        $customerEntity->expects($this->once())
            ->method($method)
            ->will($this->returnValue($customerReturnData));
        $addressEntity->expects($this->once())
            ->method($method)
            ->will($this->returnValue($addressReturnData));

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity']  = $addressEntity;
        $this->_model = new Mage_ImportExport_Model_Import_Entity_CustomerComposite($data);
        return $this->_model;
    }
}
