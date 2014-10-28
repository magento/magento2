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
namespace Magento\CustomerImportExport\Model\Import;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\File\Read;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Source\Csv;

/**
 * Customer composite test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerCompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $_customerAttributes = array('firstname', 'lastname', 'dob');

    /**
     * @var array
     */
    protected $_addressAttributes = array('city', 'country', 'street');

    /**
     * @var \Magento\Core\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreHelper;

    /**
     * @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_string;

    /**
     * @var \Magento\ImportExport\Model\ImportFactory
     */
    protected $_importFactory;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\ImportExport\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\CustomerImportExport\Model\Resource\Import\CustomerComposite\DataFactory
     */
    protected $_dataFactory;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * Expected prepared data after method CustomerComposite::_prepareRowForDb
     *
     * @var array
     */
    protected $_preparedData = array(
        '_scope' => CustomerComposite::SCOPE_DEFAULT,
        Address::COLUMN_WEBSITE => 'admin',
        Address::COLUMN_EMAIL => 'test@qwewqeq.com',
        Address::COLUMN_ADDRESS_ID => null
    );

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
        'importData'
    );

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $translateInline = $this->getMock('\Magento\Framework\Translate\InlineInterface', array(), array(), '', false);
        $translateInline->expects($this->any())->method('isAllowed')->will($this->returnValue(false));

        $context =
            $this->getMock('Magento\Framework\App\Helper\Context', array('getTranslateInline'), array(), '', false);
        $context->expects($this->any())->method('getTranslateInline')->will($this->returnValue($translateInline));

        $data = array(
            'context' => $context,
            'locale' => $this->getMock('Magento\Framework\Locale', array(), array(), '', false),
            'dateModel' => $this->getMock('Magento\Framework\Stdlib\DateTime\DateTime', array(), array(), '', false)
        );
        $this->_coreHelper = $objectManager->getObject('Magento\Core\Helper\Data', $data);
        $this->_string = new \Magento\Framework\Stdlib\String();

        $this->_importFactory = $this->getMock(
            'Magento\ImportExport\Model\ImportFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_resource = $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false);
        $this->_resourceHelper = $this->getMock(
            'Magento\ImportExport\Model\Resource\Helper',
            array(),
            array(),
            '',
            false
        );
        $this->_dataFactory = $this->getMock(
            'Magento\CustomerImportExport\Model\Resource\Import\CustomerComposite\DataFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_customerFactory = $this->getMock(
            'Magento\CustomerImportExport\Model\Import\CustomerFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_addressFactory = $this->getMock(
            'Magento\CustomerImportExport\Model\Import\AddressFactory',
            array(),
            array(),
            '',
            false
        );

        $this->_scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
    }

    /**
     * @param array $data
     * @return CustomerComposite
     */
    protected function _createModelMock($data)
    {
        return new CustomerComposite(
            $this->_coreHelper,
            $this->_string,
            $this->_scopeConfigMock,
            $this->_importFactory,
            $this->_resourceHelper,
            $this->_resource,
            $this->_dataFactory,
            $this->_customerFactory,
            $this->_addressFactory,
            $data
        );
    }

    /**
     * @return CustomerComposite
     */
    protected function _getModelMock()
    {
        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $this->_getCustomerEntityMock();
        $data['address_entity'] = $this->_getAddressEntityMock();
        return $this->_createModelMock($data);
    }

    /**
     * Returns entity mock for method testPrepareRowForDb
     *
     * @return CustomerComposite
     */
    protected function _getModelMockForPrepareRowForDb()
    {
        $customerEntity = $this->_getCustomerEntityMock(array('validateRow'));
        $customerEntity->expects($this->any())->method('validateRow')->will($this->returnValue(true));

        $customerStorage = $this->getMock('stdClass', array('getCustomerId'));
        $customerStorage->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));

        $addressEntity = $this->_getAddressEntityMock(array('validateRow', 'getCustomerStorage'));
        $addressEntity->expects($this->any())->method('validateRow')->will($this->returnValue(true));
        $addressEntity->expects(
            $this->any()
        )->method(
            'getCustomerStorage'
        )->will(
            $this->returnValue($customerStorage)
        );

        $dataSourceMock = $this->getMock('stdClass', array('cleanBunches', 'saveBunch'));
        $dataSourceMock->expects(
            $this->any()
        )->method(
            'saveBunch'
        )->will(
            $this->returnCallback(array($this, 'verifyPrepareRowForDbData'))
        );

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;
        $data['data_source_model'] = $dataSourceMock;

        return $this->_createModelMock($data);
    }

    /**
     * Returns entity mock for method testImportData
     *
     * @param bool $isDeleteBehavior
     * @param boolean $customerImport
     * @param boolean $addressImport
     * @return CustomerComposite
     */
    protected function _getModelMockForImportData($isDeleteBehavior, $customerImport, $addressImport)
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $customerEntity->expects($this->once())->method('importData')->will($this->returnValue($customerImport));

        $addressEntity = $this->_getAddressEntityMock();
        // address import starts only if customer import finished successfully
        if ($isDeleteBehavior || !$customerImport) {
            $addressEntity->expects($this->never())->method('importData');
        } else {
            $addressEntity->expects($this->once())->method('importData')->will($this->returnValue($addressImport));
        }

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        return $this->_createModelMock($data);
    }

    /**
     * @param array $mockedMethods
     * @return Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCustomerEntityMock(array $mockedMethods = null)
    {
        if (is_null($mockedMethods)) {
            $mockedMethods = $this->_entityMockedMethods;
        }
        $mockedMethods[] = 'getAttributeCollection';
        $mockedMethods[] = 'getWebsiteId';

        $customerEntity = $this->getMock(
            'Magento\CustomerImportExport\Model\Import\Customer',
            $mockedMethods,
            array(),
            '',
            false
        );

        $attributeList = array();
        foreach ($this->_customerAttributes as $code) {
            $attribute = new \Magento\Framework\Object(array('attribute_code' => $code));
            $attributeList[] = $attribute;
        }
        $customerEntity->expects(
            $this->once()
        )->method(
            'getAttributeCollection'
        )->will(
            $this->returnValue($attributeList)
        );

        return $customerEntity;
    }

    /**
     * @param array $mockedMethods
     * @return Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getAddressEntityMock(array $mockedMethods = null)
    {
        if (is_null($mockedMethods)) {
            $mockedMethods = $this->_entityMockedMethods;
        }
        $mockedMethods[] = 'getAttributeCollection';

        $addressEntity = $this->getMock(
            'Magento\CustomerImportExport\Model\Import\Address',
            $mockedMethods,
            array(),
            '',
            false
        );

        $attributeList = array();
        foreach ($this->_addressAttributes as $code) {
            $attribute = new \Magento\Framework\Object(array('attribute_code' => $code));
            $attributeList[] = $attribute;
        }
        $addressEntity->expects(
            $this->once()
        )->method(
            'getAttributeCollection'
        )->will(
            $this->returnValue($attributeList)
        );

        return $addressEntity;
    }

    /**
     * Retrieve all necessary objects mocks which used inside customer storage
     *
     * @return array
     */
    protected function _getModelDependencies()
    {
        $data = array(
            'data_source_model' => 'not_used',
            'customer_data_source_model' => 'not_used',
            'address_data_source_model' => 'not_used',
            'connection' => 'not_used',
            'helpers' => array(),
            'page_size' => 1,
            'max_data_size' => 1,
            'bunch_size' => 1,
            'collection_by_pages_iterator' => 'not_used',
            'next_customer_id' => 1
        );

        return $data;
    }

    public function testIsAttributeParticular()
    {
        $modelUnderTest = $this->_getModelMock();
        foreach ($this->_addressAttributes as $code) {
            $this->assertTrue(
                $modelUnderTest->isAttributeParticular(
                    CustomerComposite::COLUMN_ADDRESS_PREFIX . $code
                ),
                'Attribute must be particular'
            );
        }
        $this->assertFalse($modelUnderTest->isAttributeParticular('test'), 'Attribute must not be particular');
    }

    /**
     * @dataProvider getRowDataProvider
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
        $addressEntity = $this->_getAddressEntityMock();

        $customerEntity->expects($this->exactly($calls['customerValidationCalls']))
            ->method('validateRow')
            ->will($this->returnValue($validationReturn));

        $customerEntity->expects($this->any())
            ->method('getErrorMessages')
            ->will($this->returnValue(array()));

        $addressEntity
            ->expects($this->exactly($calls['addressValidationCalls']))
            ->method('validateRow')
            ->will($this->returnValue($validationReturn));

        $customerStorage = $this->getMock('stdClass', array('getCustomerId'));
        $customerStorage->expects($this->any())->method('getCustomerId')->will($this->returnValue(true));
        $addressEntity->expects(
            $this->any()
        )->method(
            'getCustomerStorage'
        )->will(
            $this->returnValue($customerStorage)
        );

        $addressEntity->expects($this->any())->method('getErrorMessages')->will($this->returnValue(array()));


        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        $modelUnderTest = $this->_createModelMock($data);
        $modelUnderTest->setParameters(array('behavior' => $behavior));

        foreach ($rows as $index => $data) {
            $modelUnderTest->validateRow($data, $index);
        }
        foreach ($expectedErrors as $error) {
            $this->assertArrayHasKey($error, $modelUnderTest->getErrorMessages());
        }
    }

    public function testPrepareAddressRowData()
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $this->_entityMockedMethods[] = 'getCustomerStorage';
        $addressEntity = $this->_getAddressEntityMock();

        $customerEntity->expects($this->once())->method('validateRow')->will($this->returnValue(true));

        $addressEntity->expects(
            $this->once()
        )->method(
            'validateRow'
        )->will(
            $this->returnCallback(array($this, 'validateAddressRowParams'))
        );

        $customerStorage = $this->getMock('stdClass', array('getCustomerId'));
        $customerStorage->expects($this->any())->method('getCustomerId')->will($this->returnValue(true));
        $addressEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->will($this->returnValue($customerStorage));

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        $modelUnderTest = $this->_createModelMock($data);

        $rowData = array(
            Customer::COLUMN_EMAIL => 'test@test.com',
            Customer::COLUMN_WEBSITE => 'admin',
            Address::COLUMN_ADDRESS_ID => null,
            CustomerComposite::COLUMN_DEFAULT_BILLING => true,
            CustomerComposite::COLUMN_DEFAULT_SHIPPING => true,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'dob' => '1984-11-11'
        );

        $modelUnderTest->validateRow($rowData, 1);
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
        $this->assertArrayHasKey(
            CustomerComposite::COLUMN_DEFAULT_BILLING,
            $rowData
        );
        $this->assertArrayHasKey(
            CustomerComposite::COLUMN_DEFAULT_SHIPPING,
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
                        Customer::COLUMN_EMAIL => 'test@test.com',
                        Customer::COLUMN_WEBSITE => 'admin',
                        Address::COLUMN_ADDRESS_ID => null
                    ),
                    array(
                        Customer::COLUMN_EMAIL => '',
                        Customer::COLUMN_WEBSITE => '',
                        Address::COLUMN_ADDRESS_ID => 1
                    )
                ),
                '$calls' => array('customerValidationCalls' => 1, 'addressValidationCalls' => 2),
                '$validationReturn' => true,
                '$expectedErrors' => array(),
                '$behavior' => Import::BEHAVIOR_APPEND
            ),
            'customer and address rows, delete behavior' => array(
                '$rows' => array(
                    array(
                        Customer::COLUMN_EMAIL => 'test@test.com',
                        Customer::COLUMN_WEBSITE => 'admin',
                        Address::COLUMN_ADDRESS_ID => null
                    ),
                    array(
                        Customer::COLUMN_EMAIL => '',
                        Customer::COLUMN_WEBSITE => '',
                        Address::COLUMN_ADDRESS_ID => 1
                    )
                ),
                '$calls' => array('customerValidationCalls' => 1, 'addressValidationCalls' => 0),
                '$validationReturn' => true,
                '$expectedErrors' => array(),
                '$behavior' => Import::BEHAVIOR_DELETE
            ),
            'customer and two addresses row, append behavior' => array(
                '$rows' => array(
                    array(
                        Customer::COLUMN_EMAIL => 'test@test.com',
                        Customer::COLUMN_WEBSITE => 'admin',
                        Address::COLUMN_ADDRESS_ID => null
                    ),
                    array(
                        Customer::COLUMN_EMAIL => '',
                        Customer::COLUMN_WEBSITE => '',
                        Address::COLUMN_ADDRESS_ID => 1
                    ),
                    array(
                        Customer::COLUMN_EMAIL => '',
                        Customer::COLUMN_WEBSITE => '',
                        Address::COLUMN_ADDRESS_ID => 2
                    )
                ),
                '$calls' => array('customerValidationCalls' => 1, 'addressValidationCalls' => 3),
                '$validationReturn' => true,
                '$expectedErrors' => array(),
                '$behavior' => Import::BEHAVIOR_APPEND
            ),
            'customer and addresses row with filed validation, append behavior' => array(
                '$rows' => array(
                    array(
                        Customer::COLUMN_EMAIL => 'test@test.com',
                        Customer::COLUMN_WEBSITE => 'admin',
                        Address::COLUMN_ADDRESS_ID => null
                    ),
                    array(
                        Customer::COLUMN_EMAIL => '',
                        Customer::COLUMN_WEBSITE => '',
                        Address::COLUMN_ADDRESS_ID => 1
                    )
                ),
                '$calls' => array('customerValidationCalls' => 1, 'addressValidationCalls' => 0),
                '$validationReturn' => false,
                '$expectedErrors' => array('Orphan rows that will be skipped due default row errors'),
                '$behavior' => Import::BEHAVIOR_APPEND
            )
        );
    }

    public function testSetParameters()
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $addressEntity = $this->_getAddressEntityMock();

        $customerEntity->expects(
            $this->once()
        )->method(
            'setParameters'
        )->will(
            $this->returnCallback(array($this, 'callbackCheckParameters'))
        );
        $addressEntity->expects(
            $this->once()
        )->method(
            'setParameters'
        )->will(
            $this->returnCallback(array($this, 'callbackCheckParameters'))
        );
        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        $modelUnderTest = $this->_createModelMock($data);

        $params = array('behavior' => Import::BEHAVIOR_APPEND);
        $modelUnderTest->setParameters($params);
    }

    /**
     * @param array $params
     */
    public function callbackCheckParameters(array $params)
    {
        $this->assertArrayHasKey('behavior', $params);
        $this->assertEquals(Import::BEHAVIOR_ADD_UPDATE, $params['behavior']);
    }

    public function testSetSource()
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $addressEntity = $this->_getAddressEntityMock();

        $customerEntity->expects($this->once())->method('setSource');
        $addressEntity->expects($this->once())->method('setSource');
        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        $modelUnderTest = $this->_createModelMock($data);

        $source = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Import\AbstractSource',
            array(),
            '',
            false
        );
        $modelUnderTest->setSource($source);
    }

    public function testGetErrorMessages()
    {
        $errorMessages = array(
            'Required field' => array(1, 2, 3),
            'Bad password' => array(1),
            'Wrong website' => array(1, 2)
        );
        $customerEntity = $this->_getCustomerEntityMock();
        $customerEntity->expects($this->once())->method('getErrorMessages')->will($this->returnValue($errorMessages));

        $errorMessages = array('Required field' => array(2, 3, 4, 5), 'Wrong address' => array(1, 2));
        $addressEntity = $this->_getAddressEntityMock();
        $addressEntity->expects($this->once())->method('getErrorMessages')->will($this->returnValue($errorMessages));

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        $modelUnderTest = $this->_createModelMock($data);

        $modelUnderTest->addRowError('Bad password', 1);

        $expectedErrors = array(
            'Required field' => array(1, 2, 3, 4, 5),
            'Bad password' => array(2),
            'Wrong website' => array(1, 2),
            'Wrong address' => array(1, 2)
        );

        $actualErrors = $modelUnderTest->getErrorMessages();
        foreach ($expectedErrors as $error => $rows) {
            $this->assertArrayHasKey($error, $actualErrors);
            $this->assertSame($rows, array_values($actualErrors[$error]));
        }
    }

    public function testPrepareRowForDb()
    {
        $modelUnderTest = $this->_getModelMockForPrepareRowForDb();
        $pathToCsvFile = __DIR__ . '/_files/customer_composite_prepare_row_for_db.csv';
        $directoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\Write', array(), array(), '', false);
        $directoryMock->expects($this->any())
            ->method('openFile')->will(
            $this->returnValue(new Read($pathToCsvFile, new File()))
        );
        $source = new Csv($pathToCsvFile, $directoryMock);
        $modelUnderTest->setSource($source);
        $modelUnderTest->validateData();
    }

    /**
     * Callback for \Magento\ImportExport\Model\Resource\Import\Data::saveBunch to verify correctness of data
     * for method CustomerComposite::_prepareRowForDb
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
                '$behavior' => Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => true,
                '$addressImport' => true,
                '$result' => true
            ),
            'add_update_behavior_customer_true_address_false' => array(
                '$behavior' => Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => true,
                '$addressImport' => false,
                '$result' => false
            ),
            'add_update_behavior_customer_false_address_true' => array(
                '$behavior' => Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => false,
                '$addressImport' => true,
                '$result' => false
            ),
            'add_update_behavior_customer_false_address_false' => array(
                '$behavior' => Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => false,
                '$addressImport' => false,
                '$result' => false
            ),
            'delete_behavior_customer_true' => array(
                '$behavior' => Import::BEHAVIOR_DELETE,
                '$customerImport' => true,
                '$addressImport' => false,
                '$result' => true
            ),
            'delete_behavior_customer_false' => array(
                '$behavior' => Import::BEHAVIOR_DELETE,
                '$customerImport' => false,
                '$addressImport' => false,
                '$result' => false
            )
        );
    }

    /**
     * @dataProvider dataProviderTestImportData
     *
     * @param string $behavior
     * @param boolean $customerImport
     * @param boolean $addressImport
     * @param boolean $result
     */
    public function testImportData($behavior, $customerImport, $addressImport, $result)
    {
        $isDeleteBehavior = $behavior == Import::BEHAVIOR_DELETE;
        $entityMock = $this->_getModelMockForImportData($isDeleteBehavior, $customerImport, $addressImport);
        $entityMock->setParameters(array('behavior' => $behavior));
        $importResult = $entityMock->importData();
        if ($result) {
            $this->assertTrue($importResult);
        } else {
            $this->assertFalse($importResult);
        }
    }

    public function testGetErrorsCount()
    {
        $customerReturnData = 1;
        $addressReturnData = 2;
        $model = $this->_getModelForGetterTest('getErrorsCount', $customerReturnData, $addressReturnData);
        $model->addRowError(CustomerComposite::ERROR_ROW_IS_ORPHAN, 1);

        $this->assertEquals($customerReturnData + $addressReturnData + 1, $model->getErrorsCount());
    }

    public function testGetInvalidRowsCount()
    {
        $customerReturnData = 3;
        $addressReturnData = 2;
        $model = $this->_getModelForGetterTest('getInvalidRowsCount', $customerReturnData, $addressReturnData);
        $model->addRowError(CustomerComposite::ERROR_ROW_IS_ORPHAN, 1);

        $this->assertEquals($customerReturnData + $addressReturnData + 1, $model->getInvalidRowsCount());
    }

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
     * @return CustomerComposite
     */
    protected function _getModelForGetterTest($method, $customerReturnData, $addressReturnData)
    {
        $customerEntity = $this->_getCustomerEntityMock();
        $addressEntity = $this->_getAddressEntityMock();

        $customerEntity->expects($this->once())->method($method)->will($this->returnValue($customerReturnData));
        $addressEntity->expects($this->once())->method($method)->will($this->returnValue($addressReturnData));

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        return $this->_createModelMock($data);
    }
}
