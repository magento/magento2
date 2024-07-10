<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Test\Unit\Model\Import;

use Magento\Customer\Model\Indexer\Processor;
use Magento\CustomerImportExport\Model\Import\Address;
use Magento\CustomerImportExport\Model\Import\AddressFactory;
use Magento\CustomerImportExport\Model\Import\Customer;
use Magento\CustomerImportExport\Model\Import\CustomerComposite;
use Magento\CustomerImportExport\Model\Import\CustomerFactory;
use Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\InlineInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The test for Customer composite model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerCompositeTest extends TestCase
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
     * @var StringUtils|MockObject
     */
    protected $_string;

    /**
     * @var ImportFactory
     */
    protected $_importFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * @var Helper
     */
    protected $_resourceHelper;

    /**
     * @var DataFactory
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
     * @var ScopeConfigInterface|MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var ProcessingErrorAggregatorInterface|MockObject
     */
    protected $errorAggregator;

    /**
     * @var ProcessingError $newError
     */
    protected $error;

    /**
     * @var ProcessingErrorFactory|MockObject
     */
    protected $errorFactory;

    /**
     * @var Processor|MockObject
     */
    private $indexerProcessor;

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

    protected function setUp(): void
    {
        $translateInline = $this->getMockForAbstractClass(InlineInterface::class);
        $translateInline->expects($this->any())->method('isAllowed')->willReturn(false);

        $context =
            $this->getMockBuilder(Context::class)
                ->addMethods(['getTranslateInline'])
                ->disableOriginalConstructor()
                ->getMock();
        $context->expects($this->any())->method('getTranslateInline')->willReturn($translateInline);

        $this->_string = new StringUtils();

        $this->_importFactory = $this->createMock(ImportFactory::class);
        $this->_resource = $this->createMock(ResourceConnection::class);
        $this->_resourceHelper = $this->createMock(Helper::class);
        $this->_dataFactory = $this->createMock(
            DataFactory::class
        );
        $this->_customerFactory = $this->createMock(CustomerFactory::class);
        $this->_addressFactory = $this->createMock(AddressFactory::class);

        $this->errorFactory = $this->createPartialMock(
            ProcessingErrorFactory::class,
            ['create']
        );

        $this->error = $this->createPartialMock(
            ProcessingError::class,
            ['init']
        );

        $this->errorFactory->expects($this->any())->method('create')->willReturn($this->error);
        $this->error->expects($this->any())->method('init')->willReturn(true);

        $this->errorAggregator = $this->getMockBuilder(
            ProcessingErrorAggregator::class
        )
            ->setMethods(['hasToBeTerminated'])
            ->setConstructorArgs([$this->errorFactory])
            ->getMock();

        $this->_scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->indexerProcessor = $this->createMock(Processor::class);
    }

    /**
     * @param array $data
     * @return CustomerComposite
     */
    protected function _createModelMock($data)
    {
        return new CustomerComposite(
            $this->_string,
            $this->_scopeConfigMock,
            $this->_importFactory,
            $this->_resourceHelper,
            $this->_resource,
            $this->errorAggregator,
            $this->_dataFactory,
            $this->_customerFactory,
            $this->_addressFactory,
            $this->indexerProcessor,
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
        $customerStorage = $this->getMockBuilder('stdClass')
            ->addMethods(['getCustomerId', 'prepareCustomers', 'addCustomer'])
            ->getMock();
        $customerStorage->expects($this->any())->method('getCustomerId')->willReturn(1);
        $customerEntity = $this->_getCustomerEntityMock();
        $customerEntity->expects($this->any())->method('validateRow')->willReturn(true);
        $customerEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->willReturn($customerStorage);
        $customerEntity->expects($this->any())
            ->method('getValidColumnNames')
            ->willReturn(['cols']);

        $addressEntity = $this->_getAddressEntityMock();
        $addressEntity->expects($this->any())->method('validateRow')->willReturn(true);
        $addressEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->willReturn($customerStorage);

        $dataSourceMock = $this->getMockBuilder(\stdClass::class)->addMethods(['cleanBunches', 'saveBunch'])
            ->disableOriginalConstructor()
            ->getMock();
        $dataSourceMock->expects($this->any())
            ->method('saveBunch')
            ->willReturnCallback([$this, 'verifyPrepareRowForDbData']);

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
        $customerEntity->expects($this->once())->method('setIds')->willReturnSelf();
        $customerEntity->expects($this->once())->method('importData')->willReturn($customerImport);

        $addressEntity = $this->_getAddressEntityMock();
        // address import starts only if customer import finished successfully
        if ($isDeleteBehavior || !$customerImport) {
            $addressEntity->expects($this->never())->method('importData');
        } else {
            $addressEntity->expects($this->atMost(2))->method('setCustomerAttributes')->willReturnSelf();
            $addressEntity->expects($this->once())->method('setIds')->willReturnSelf();
            $addressEntity->expects($this->once())->method('importData')->willReturn($addressImport);
        }

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        $obj = $this->_createModelMock($data);
        $obj->setIds([1, 2]);
        return $obj;
    }

    /**
     * @return Customer|MockObject
     */
    protected function _getCustomerEntityMock()
    {
        $customerEntity = $this->createMock(Customer::class);

        $attributeList = [];
        foreach ($this->_customerAttributes as $code) {
            $attribute = new DataObject(['attribute_code' => $code]);
            $attributeList[] = $attribute;
        }
        $customerEntity->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($attributeList);

        return $customerEntity;
    }

    /**
     * @return Address|MockObject
     */
    private function _getAddressEntityMock()
    {
        $addressEntity = $this->createMock(Address::class);

        $attributeList = [];
        foreach ($this->_addressAttributes as $code) {
            $attribute = new DataObject(['attribute_code' => $code]);
            $attributeList[] = $attribute;
        }
        $addressEntity->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($attributeList);

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
        $addressEntity = $this->_getAddressEntityMock();

        $customerEntity->expects($this->exactly($calls['customerValidationCalls']))
            ->method('validateRow')
            ->willReturn($validationReturn);

        $addressEntity
            ->expects($this->exactly($calls['addressValidationCalls']))
            ->method('validateRow')
            ->willReturn($validationReturn);

        $customerStorage = $this->getMockBuilder(\stdClass::class)->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerStorage->expects($this->any())->method('getCustomerId')->willReturn(true);
        $addressEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->willReturn($customerStorage);

        $customerEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->willReturn($customerStorage);

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
        $addressEntity = $this->_getAddressEntityMock();

        $customerEntity->expects($this->once())->method('validateRow')->willReturn(true);

        $addressEntity->expects($this->once())
            ->method('validateRow')
            ->willReturnCallback([$this, 'validateAddressRowParams']);

        $customerStorage = $this->getMockBuilder(\stdClass::class)->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerStorage->expects($this->any())->method('getCustomerId')->willReturn(true);
        $addressEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->willReturn($customerStorage);
        $customerEntity->expects($this->any())
            ->method('getCustomerStorage')
            ->willReturn($customerStorage);

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

        $customerEntity->expects($this->once())
            ->method('setParameters')
            ->willReturnCallback([$this, 'callbackCheckParameters']);
        $addressEntity->expects($this->once())
            ->method('setParameters')
            ->willReturnCallback([$this, 'callbackCheckParameters']);
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
            AbstractSource::class,
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
        $directoryMock = $this->createMock(Write::class);
        $directoryMock->expects($this->any())
            ->method('openFile')
            ->willReturn(new Read($pathToCsvFile, new File()));
        $directoryMock->expects($this->any())
            ->method('getRelativePath')
            ->with($pathToCsvFile)
            ->willReturn($pathToCsvFile);
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

        $customerEntity->expects($this->once())->method($method)->willReturn($customerReturnData);
        $addressEntity->expects($this->once())->method($method)->willReturn($addressReturnData);

        $data = $this->_getModelDependencies();
        $data['customer_entity'] = $customerEntity;
        $data['address_entity'] = $addressEntity;

        return $this->_createModelMock($data);
    }
}
