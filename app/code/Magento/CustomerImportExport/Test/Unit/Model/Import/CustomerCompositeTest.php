<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CustomerImportExport\Test\Unit\Model\Import;

use Magento\CustomerImportExport\Model\Import\CustomerComposite;
use Magento\CustomerImportExport\Model\Import\Customer;
use Magento\CustomerImportExport\Model\Import\Address;
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
    protected $_customerAttributes = ['firstname', 'lastname', 'dob'];

    /**
     * @var array
     */
    protected $_addressAttributes = ['city', 'country', 'street'];

    /**
     * @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_string;

    /**
     * @var \Magento\ImportExport\Model\ImportFactory
     */
    protected $_importFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory
     */
    protected $_dataFactory;

    /**
     * @var \Magento\CustomerImportExport\Model\Import\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\CustomerImportExport\Model\Import\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $errorAggregator;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError $newError
     */
    protected $error;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $errorFactory;

    /**
     * Expected prepared data after method CustomerComposite::_prepareRowForDb
     *
     * @var array
     */
    protected $_preparedData = [
        '_scope' => CustomerComposite::SCOPE_DEFAULT,
        Address::COLUMN_WEBSITE => 'admin',
        Address::COLUMN_EMAIL => 'test@qwewqeq.com',
        Address::COLUMN_ADDRESS_ID => null,
    ];

    /**
     * List of mocked methods for customer and address entity adapters
     *
     * @var array
     */
    protected $_entityMockedMethods = [
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
    ];

    protected function setUp()
    {
        $translateInline = $this->getMock(\Magento\Framework\Translate\InlineInterface::class, [], [], '', false);
        $translateInline->expects($this->any())->method('isAllowed')->will($this->returnValue(false));

        $context =
            $this->getMock(\Magento\Framework\App\Helper\Context::class, ['getTranslateInline'], [], '', false);
        $context->expects($this->any())->method('getTranslateInline')->will($this->returnValue($translateInline));

        $this->_string = new \Magento\Framework\Stdlib\StringUtils();

        $this->_importFactory = $this->getMock(
            \Magento\ImportExport\Model\ImportFactory::class,
            [],
            [],
            '',
            false
        );
        $this->_resource = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $this->_resourceHelper = $this->getMock(
            \Magento\ImportExport\Model\ResourceModel\Helper::class,
            [],
            [],
            '',
            false
        );
        $this->_dataFactory = $this->getMock(
            \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory::class,
            [],
            [],
            '',
            false
        );
        $this->_customerFactory = $this->getMock(
            \Magento\CustomerImportExport\Model\Import\CustomerFactory::class,
            [],
            [],
            '',
            false
        );
        $this->_addressFactory = $this->getMock(
            \Magento\CustomerImportExport\Model\Import\AddressFactory::class,
            [],
            [],
            '',
            false
        );

        $this->errorFactory = $this->getMock(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->error = $this->getMock(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::class,
            ['init'],
            [],
            '',
            false
        );

        $this->errorFactory->expects($this->any())->method('create')->will($this->returnValue($this->error));
        $this->error->expects($this->any())->method('init')->will($this->returnValue(true));

        $this->errorAggregator = $this->getMock(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator::class,
            ['hasToBeTerminated'],
            [$this->errorFactory],
            '',
            true
        );

        $this->_scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
    }

    /**
     * @param array $data
     * @return CustomerComposite
     */
    protected function _createModelMock($data)
    {
        return new \Magento\CustomerImportExport\Model\Import\CustomerComposite(
            $this->_string,
            $this->_scopeConfigMock,
            $this->_importFactory,
            $this->_resourceHelper,
            $this->_resource,
            $this->errorAggregator,
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
        $customerEntity = $this->_getCustomerEntityMock(['validateRow']);
        $customerEntity->expects($this->any())->method('validateRow')->will($this->returnValue(true));

        $customerStorage = $this->getMock(\stdClass::class, ['getCustomerId']);
        $customerStorage->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));

        $addressEntity = $this->_getAddressEntityMock(['validateRow', 'getCustomerStorage']);
        $addressEntity->expects($this->any())->method('validateRow')->will($this->returnValue(true));
        $addressEntity->expects(
            $this->any()
        )->method(
            'getCustomerStorage'
        )->will(
            $this->returnValue($customerStorage)
        );

        $dataSourceMock = $this->getMock(\stdClass::class, ['cleanBunches', 'saveBunch']);
        $dataSourceMock->expects(
            $this->any()
        )->method(
            'saveBunch'
        )->will(
            $this->returnCallback([$this, 'verifyPrepareRowForDbData'])
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
            \Magento\CustomerImportExport\Model\Import\Customer::class,
            $mockedMethods,
            [],
            '',
            false
        );

        $attributeList = [];
        foreach ($this->_customerAttributes as $code) {
            $attribute = new \Magento\Framework\DataObject(['attribute_code' => $code]);
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
            \Magento\CustomerImportExport\Model\Import\Address::class,
            $mockedMethods,
            [],
            '',
            false
        );

        $attributeList = [];
        foreach ($this->_addressAttributes as $code) {
            $attribute = new \Magento\Framework\DataObject(['attribute_code' => $code]);
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
        $data = [
            'data_source_model' => 'not_used',
            'customer_data_source_model' => 'not_used',
            'address_data_source_model' => 'not_used',
            'connection' => 'not_used',
            'helpers' => [],
            'page_size' => 1,
            'max_data_size' => 1,
            'bunch_size' => 1,
            'collection_by_pages_iterator' => 'not_used',
            'next_customer_id' => 1,
        ];

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
            ->will($this->returnValue([]));

        $addressEntity
            ->expects($this->exactly($calls['addressValidationCalls']))
            ->method('validateRow')
            ->will($this->returnValue($validationReturn));

        $customerStorage = $this->getMock(\stdClass::class, ['getCustomerId']);
        $customerStorage->expects($this->any())->method('getCustomerId')->will($this->returnValue(true));
        $addressEntity->expects(
            $this->any()
        )->method(
            'getCustomerStorage'
        )->will(
            $this->returnValue($customerStorage)
        );

        $addressEntity->expects($this->any())->method('getErrorMessages')->will($this->returnValue([]));

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        $modelUnderTest = $this->_createModelMock($data);
        $modelUnderTest->setParameters(['behavior' => $behavior]);

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
            $this->returnCallback([$this, 'validateAddressRowParams'])
        );

        $customerStorage = $this->getMock(\stdClass::class, ['getCustomerId']);
        $customerStorage->expects($this->any())->method('getCustomerId')->will($this->returnValue(true));
        $addressEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->will($this->returnValue($customerStorage));

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        $modelUnderTest = $this->_createModelMock($data);

        $rowData = [
            Customer::COLUMN_EMAIL => 'test@test.com',
            Customer::COLUMN_WEBSITE => 'admin',
            Address::COLUMN_ADDRESS_ID => null,
            CustomerComposite::COLUMN_DEFAULT_BILLING => true,
            CustomerComposite::COLUMN_DEFAULT_SHIPPING => true,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'dob' => '1984-11-11',
        ];

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
        return [
            'customer and address rows, append behavior' => [
                '$rows' => [
                    [
                        Customer::COLUMN_EMAIL => 'test@test.com',
                        Customer::COLUMN_WEBSITE => 'admin',
                        Address::COLUMN_ADDRESS_ID => null,
                    ],
                    [
                        Customer::COLUMN_EMAIL => '',
                        Customer::COLUMN_WEBSITE => '',
                        Address::COLUMN_ADDRESS_ID => 1
                    ],
                ],
                '$calls' => ['customerValidationCalls' => 1, 'addressValidationCalls' => 2],
                '$validationReturn' => true,
                '$expectedErrors' => [],
                '$behavior' => Import::BEHAVIOR_APPEND,
            ],
            'customer and address rows, delete behavior' => [
                '$rows' => [
                    [
                        Customer::COLUMN_EMAIL => 'test@test.com',
                        Customer::COLUMN_WEBSITE => 'admin',
                        Address::COLUMN_ADDRESS_ID => null,
                    ],
                    [
                        Customer::COLUMN_EMAIL => '',
                        Customer::COLUMN_WEBSITE => '',
                        Address::COLUMN_ADDRESS_ID => 1
                    ],
                ],
                '$calls' => ['customerValidationCalls' => 1, 'addressValidationCalls' => 0],
                '$validationReturn' => true,
                '$expectedErrors' => [],
                '$behavior' => Import::BEHAVIOR_DELETE,
            ],
            'customer and two addresses row, append behavior' => [
                '$rows' => [
                    [
                        Customer::COLUMN_EMAIL => 'test@test.com',
                        Customer::COLUMN_WEBSITE => 'admin',
                        Address::COLUMN_ADDRESS_ID => null,
                    ],
                    [
                        Customer::COLUMN_EMAIL => '',
                        Customer::COLUMN_WEBSITE => '',
                        Address::COLUMN_ADDRESS_ID => 1
                    ],
                    [
                        Customer::COLUMN_EMAIL => '',
                        Customer::COLUMN_WEBSITE => '',
                        Address::COLUMN_ADDRESS_ID => 2
                    ],
                ],
                '$calls' => ['customerValidationCalls' => 1, 'addressValidationCalls' => 3],
                '$validationReturn' => true,
                '$expectedErrors' => [],
                '$behavior' => Import::BEHAVIOR_APPEND,
            ],
        ];
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
            $this->returnCallback([$this, 'callbackCheckParameters'])
        );
        $addressEntity->expects(
            $this->once()
        )->method(
            'setParameters'
        )->will(
            $this->returnCallback([$this, 'callbackCheckParameters'])
        );
        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        $modelUnderTest = $this->_createModelMock($data);

        $params = ['behavior' => Import::BEHAVIOR_APPEND];
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
            \Magento\ImportExport\Model\Import\AbstractSource::class,
            [],
            '',
            false
        );
        $modelUnderTest->setSource($source);
    }

    public function testPrepareRowForDb()
    {
        $modelUnderTest = $this->_getModelMockForPrepareRowForDb();
        $pathToCsvFile = __DIR__ . '/_files/customer_composite_prepare_row_for_db.csv';
        $directoryMock = $this->getMock(\Magento\Framework\Filesystem\Directory\Write::class, [], [], '', false);
        $directoryMock->expects($this->any())
            ->method('openFile')->will(
            $this->returnValue(new Read($pathToCsvFile, new File()))
        );
        $source = new Csv($pathToCsvFile, $directoryMock);
        $modelUnderTest->setSource($source);
        $modelUnderTest->validateData();
    }

    /**
     * Callback for \Magento\ImportExport\Model\ResourceModel\Import\Data::saveBunch to verify correctness of data
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
        return [
            'add_update_behavior_customer_true_address_true' => [
                '$behavior' => Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => true,
                '$addressImport' => true,
                '$result' => true,
            ],
            'add_update_behavior_customer_true_address_false' => [
                '$behavior' => Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => true,
                '$addressImport' => false,
                '$result' => false,
            ],
            'add_update_behavior_customer_false_address_true' => [
                '$behavior' => Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => false,
                '$addressImport' => true,
                '$result' => false,
            ],
            'add_update_behavior_customer_false_address_false' => [
                '$behavior' => Import::BEHAVIOR_ADD_UPDATE,
                '$customerImport' => false,
                '$addressImport' => false,
                '$result' => false,
            ],
            'delete_behavior_customer_true' => [
                '$behavior' => Import::BEHAVIOR_DELETE,
                '$customerImport' => true,
                '$addressImport' => false,
                '$result' => true,
            ],
            'delete_behavior_customer_false' => [
                '$behavior' => Import::BEHAVIOR_DELETE,
                '$customerImport' => false,
                '$addressImport' => false,
                '$result' => false,
            ]
        ];
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
        $entityMock->setParameters(['behavior' => $behavior]);
        $importResult = $entityMock->importData();
        if ($result) {
            $this->assertTrue($importResult);
        } else {
            $this->assertFalse($importResult);
        }
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
