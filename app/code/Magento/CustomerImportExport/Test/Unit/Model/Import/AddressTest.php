<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Test\Unit\Model\Import;

use Magento\Customer\Model\Address\Validator\Postcode;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Indexer\Processor;
use Magento\Customer\Model\ResourceModel\Address\Attribute as AddressAttribute;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\CustomerImportExport\Model\Import\Address;
use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage;
use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\UniversalFactory;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\CustomerImportExport\Model\Import\Address.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends TestCase
{
    /**
     * Customer address entity adapter mock
     *
     * @var Address|MockObject
     */
    protected $_model;

    /**
     * Websites array (website id => code)
     *
     * @var array
     */
    protected $_websites = [1 => 'website1', 2 => 'website2'];

    /** @var MockObject|StoreManager */
    protected $_storeManager;

    /**
     * Attributes array
     *
     * @var array
     */
    protected $_attributes = [
        'country_id' => [
            'id' => 1,
            'attribute_code' => 'country_id',
            'table' => '',
            'is_required' => true,
            'is_static' => false,
            'validate_rules' => false,
            'type' => 'select',
            'attribute_options' => null,
        ],
    ];

    /**
     * Customers array
     *
     * @var array
     */
    protected $_customers = [
        ['entity_id' => 1, 'email' => 'test1@email.com', 'website_id' => 1],
        ['entity_id' => 2, 'email' => 'test2@email.com', 'website_id' => 2],
    ];

    /**
     * Customer addresses array
     *
     * @var array
     */
    protected $_addresses = [1 => ['id' => 1, 'parent_id' => 1]];

    /**
     * Customers array
     *
     * @var array
     */
    protected $_regions = [
        ['id' => 1, 'country_id' => 'c1', 'code' => 'code1', 'default_name' => 'region1'],
        ['id' => 2, 'country_id' => 'c1', 'code' => 'code2', 'default_name' => 'region2'],
    ];

    /**
     * Available behaviours
     *
     * @var array
     */
    protected $_availableBehaviors = [
        Import::BEHAVIOR_ADD_UPDATE,
        Import::BEHAVIOR_DELETE,
        Import::BEHAVIOR_CUSTOM,
    ];

    /**
     * Customer behaviours parameters
     *
     * @var array
     */
    protected $_customBehaviour = ['update_id' => 1, 'delete_id' => 2];

    /**
     * @var StringUtils
     */
    protected $_stringLib;

    /**
     * @var ObjectManager
     */
    protected $_objectManagerMock;

    /**
     * @var ProcessingErrorAggregatorInterface|MockObject
     */
    protected $errorAggregator;

    /**
     * @var AddressAttribute\Source\CountryWithWebsites|MockObject
     */
    private $countryWithWebsites;

    /**
     * Init entity adapter model
     */
    protected function setUp(): void
    {
        $this->_objectManagerMock = new ObjectManager($this);
        $this->_stringLib = new StringUtils();
        $this->_storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsites'])
            ->getMock();
        $this->_storeManager
            ->method('getWebsites')
            ->willReturnCallback([$this, 'getWebsites']);
        $this->countryWithWebsites = $this
            ->getMockBuilder(CountryWithWebsites::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->countryWithWebsites

            ->method('getAllOptions')
            ->willReturn([]);
        $this->_model = $this->_getModelMock();
        $this->errorAggregator = $this->createPartialMock(
            ProcessingErrorAggregator::class,
            ['hasToBeTerminated']
        );
    }

    /**
     * Unset entity adapter model
     */
    protected function tearDown(): void
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
        $dataSourceModel = $this->getMockBuilder(\stdClass::class)->addMethods(['getNextBunch'])
            ->disableOriginalConstructor()
            ->getMock();
        $connection = $this->createMock(\stdClass::class);
        $attributeCollection = $this->_createAttrCollectionMock();
        $customerStorage = $this->_createCustomerStorageMock();
        $customerEntity = $this->_createCustomerEntityMock();
        $addressCollection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        foreach ($this->_addresses as $address) {
            $addressCollection->addItem(new DataObject($address));
        }

        $regionCollection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        foreach ($this->_regions as $region) {
            $regionCollection->addItem(new DataObject($region));
        }

        $data = [
            'data_source_model' => $dataSourceModel,
            'connection' => $connection,
            'page_size' => 1,
            'max_data_size' => 1,
            'bunch_size' => 1,
            'attribute_collection' => $attributeCollection,
            'entity_type_id' => 1,
            'customer_storage' => $customerStorage,
            'customer_entity' => $customerEntity,
            'address_collection' => $addressCollection,
            'entity_table' => 'not_used',
            'region_collection' => $regionCollection,
        ];

        return $data;
    }

    /**
     * Create mock of attribute collection, so it can be used for tests
     *
     * @return MockObject|\Magento\Framework\Data\Collection
     */
    protected function _createAttrCollectionMock()
    {
        $entityFactory = $this->createMock(EntityFactory::class);
        $attributeCollection = $this->getMockBuilder(Collection::class)
            ->setMethods(['getEntityTypeCode'])
            ->setConstructorArgs([$entityFactory])
            ->getMock();
        foreach ($this->_attributes as $attributeData) {
            $arguments = $this->_objectManagerMock->getConstructArguments(
                AbstractAttribute::class,
                [
                    $this->createMock(Context::class),
                    $this->createMock(Registry::class),
                    $this->createMock(Config::class),
                    $this->createMock(TypeFactory::class),
                    $this->createMock(StoreManager::class),
                    $this->createMock(Helper::class),
                    $this->createMock(UniversalFactory::class)
                ]
            );
            $arguments['data'] = $attributeData;
            $attribute = $this->getMockForAbstractClass(
                AbstractAttribute::class,
                $arguments,
                '',
                true,
                true,
                true,
                ['_construct', 'getBackend', 'getTable']
            );
            $attribute->method('getBackend')->willReturnSelf();
            $attribute->method('getTable')->willReturn($attributeData['table']);
            $attributeCollection->addItem($attribute);
        }
        return $attributeCollection;
    }

    /**
     * Create mock of customer storage, so it can be used for tests
     *
     * @return MockObject
     */
    protected function _createCustomerStorageMock()
    {
        /** @var $customerStorage Storage|MockObject */
        $customerStorage = $this->createMock(Storage::class);
        $customerStorage
            ->method('getCustomerId')
            ->willReturnCallback(
                function ($email, $websiteId) {
                    foreach ($this->_customers as $customerData) {
                        if ($customerData['email'] === $email
                            && $customerData['website_id'] === $websiteId
                        ) {
                            return $customerData['entity_id'];
                        }
                    }

                    return false;
                }
            );
        $customerStorage->method('prepareCustomers');

        return $customerStorage;
    }

    /**
     * Create simple mock of customer entity, so it can be used for tests
     *
     * @return MockObject
     */
    protected function _createCustomerEntityMock()
    {
        $customerEntity = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['filterEntityCollection', 'setParameters'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerEntity->method('filterEntityCollection')->willReturnArgument(0);
        $customerEntity->method('setParameters')->willReturnSelf();
        return $customerEntity;
    }

    /**
     * Get websites stub
     *
     * @param bool $withDefault
     * @return array
     */
    public function getWebsites($withDefault = false)
    {
        $websites = [];
        foreach ($this->_websites as $id => $code) {
            if (!$withDefault && $id == Store::DEFAULT_STORE_ID) {
                continue;
            }
            $websiteData = ['id' => $id, 'code' => $code];
            $websites[$id] = new DataObject($websiteData);
        }

        return $websites;
    }

    /**
     * Iterate stub
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param int $pageSize
     * @param array $callbacks
     */
    public function iterate(Collection $collection, $pageSize, array $callbacks)
    {
        foreach ($collection as $customer) {
            foreach ($callbacks as $callback) {
                call_user_func($callback, $customer);
            }
        }
    }

    /**
     * Create mock for customer address model class
     *
     * @return Address|MockObject
     */
    protected function _getModelMock()
    {
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $modelMock = new Address(
            $this->_stringLib,
            $scopeConfig,
            $this->createMock(ImportFactory::class),
            $this->createMock(\Magento\ImportExport\Model\ResourceModel\Helper::class),
            $this->createMock(ResourceConnection::class),
            $this->createMock(
                ProcessingErrorAggregatorInterface::class
            ),
            $this->_storeManager,
            $this->createMock(Factory::class),
            $this->createMock(Config::class),
            $this->createMock(StorageFactory::class),
            $this->createMock(AddressFactory::class),
            $this->createMock(CollectionFactory::class),
            $this->createMock(CustomerFactory::class),
            $this->createMock(\Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory::class),
            new DateTime(),
            $this->createMock(Postcode::class),
            $this->_getModelDependencies(),
            $this->countryWithWebsites,
            $this->createMock(\Magento\CustomerImportExport\Model\ResourceModel\Import\Address\Storage::class),
            $this->createMock(Processor::class)
        );

        $property = new \ReflectionProperty($modelMock, '_availableBehaviors');
        $property->setAccessible(true);
        $property->setValue($modelMock, $this->_availableBehaviors);

        return $modelMock;
    }

    /**
     * Data provider of row data and errors for add/update action
     *
     * @return array
     */
    public function validateRowForUpdateDataProvider()
    {
        return [
            'valid' => [
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_valid.php',
                '$errors' => [],
                '$isValid' => true,
            ],
            'empty address id' => [
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_empty_address_id.php',
                '$errors' => [],
                '$isValid' => true,
            ],
        ];
    }

    /**
     * Data provider of row data and errors for add/update action
     *
     * @return array
     */
    public function validateRowForDeleteDataProvider()
    {
        return [
            'valid' => [
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_valid.php',
                '$errors' => [],
                '$isValid' => true,
            ],
        ];
    }

    /**
     * @dataProvider validateRowForUpdateDataProvider
     *
     * @param array $rowData
     * @param array $errors
     * @param boolean $isValid
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testValidateRowForUpdate(array $rowData, array $errors, $isValid = false)
    {
        $this->_model->setParameters(['behavior' => Import::BEHAVIOR_ADD_UPDATE]);

        if ($isValid) {
            $this->assertTrue($this->_model->validateRow($rowData, 0));
        } else {
            $this->assertFalse($this->_model->validateRow($rowData, 0));
        }
    }

    /**
     * Test Address::validateRow()
     * with 2 rows with identical PKs in case when add/update behavior is performed
     *
     * @covers \Magento\CustomerImportExport\Model\Import\Address::validateRow
     * @covers \Magento\CustomerImportExport\Model\Import\Address::_validateRowForUpdate
     */
    public function testValidateRowForUpdateDuplicateRows()
    {
        $behavior = Import::BEHAVIOR_ADD_UPDATE;

        $this->_model->setParameters(['behavior' => $behavior]);

        $firstRow = [
            '_website' => 'website1',
            '_email' => 'test1@email.com',
            '_entity_id' => '1',
            'city' => 'Culver City',
            'company' => '',
            'country_id' => 'C1',
            'fax' => '',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'middlename' => '',
            'postcode' => '90232',
            'prefix' => '',
            'region' => 'region1',
            'region_id' => '1',
            'street' => '10441 Jefferson Blvd. Suite 200 Culver City',
            'suffix' => '',
            'telephone' => '12312313',
            'vat_id' => '',
            'vat_is_valid' => '',
            'vat_request_date' => '',
            'vat_request_id' => '',
            'vat_request_success' => '',
            '_address_default_billing_' => '1',
            '_address_default_shipping_' => '1',
        ];
        $this->assertTrue($this->_model->validateRow($firstRow, 0));
    }

    /**
     * Test Address::validateRow() with delete action
     *
     * @covers \Magento\CustomerImportExport\Model\Import\Address::validateRow
     * @dataProvider validateRowForDeleteDataProvider
     *
     * @param array $rowData
     * @param array $errors
     * @param boolean $isValid
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testValidateRowForDelete(array $rowData, array $errors, $isValid = false)
    {
        $this->_model->setParameters(['behavior' => Import::BEHAVIOR_DELETE]);

        if ($isValid) {
            $this->assertTrue($this->_model->validateRow($rowData, 0));
        } else {
            $this->assertFalse($this->_model->validateRow($rowData, 0));
        }
    }

    /**
     * Test entity type code getter
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals('customer_address', $this->_model->getEntityTypeCode());
    }

    /**
     * Test default address attribute mapping array
     */
    public function testGetDefaultAddressAttributeMapping()
    {
        $attributeMapping = $this->_model->getDefaultAddressAttributeMapping();
        $this->assertIsArray($attributeMapping, 'Default address attribute mapping must be an array.');
        $this->assertArrayHasKey(
            Address::COLUMN_DEFAULT_BILLING,
            $attributeMapping,
            'Default address attribute mapping array must have a default billing column.'
        );
        $this->assertArrayHasKey(
            Address::COLUMN_DEFAULT_SHIPPING,
            $attributeMapping,
            'Default address attribute mapping array must have a default shipping column.'
        );
    }

    /**
     * Validation method for _saveAddressEntities (callback for _saveAddressEntities)
     *
     * @param array $addRows
     * @param array $updateRows
     * @return Address|MockObject
     */
    public function validateSaveAddressEntities(array $addRows, array $updateRows)
    {
        $this->assertCount(0, $addRows);
        $this->assertCount(1, $updateRows);
        $this->assertContains($this->_customBehaviour['update_id'], $updateRows);
        return $this->_model;
    }

    /**
     * Validation method for _deleteAddressEntities (callback for _deleteAddressEntities)
     *
     * @param array $deleteRowIds
     * @return Address|MockObject
     */
    public function validateDeleteAddressEntities(array $deleteRowIds)
    {
        $this->assertCount(1, $deleteRowIds);
        $this->assertContains($this->_customBehaviour['delete_id'], $deleteRowIds);
        return $this->_model;
    }
}
