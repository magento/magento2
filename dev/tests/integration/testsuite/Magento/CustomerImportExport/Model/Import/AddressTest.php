<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\CustomerImportExport\Model\Import\Address
 */
namespace Magento\CustomerImportExport\Model\Import;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\Indexer\Processor;
use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import as ImportModel;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Indexer\StateInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends TestCase
{
    /**
     * Tested class name
     *
     * @var string
     */
    protected $_testClassName = Address::class;

    /**
     * Fixture key from fixture
     *
     * @var string
     */
    protected $_fixtureKey = '_fixture/Magento_ImportExport_Customers_Array';

    /**
     * Address entity adapter instance
     *
     * @var Address
     */
    protected $_entityAdapter;

    /**
     * Important data from address_import_update.csv (postcode is key)
     *
     * @var array
     */
    protected $_updateData = [
        'address' => [ // address records
            'update' => '19107',  // address with updates
            'new' => '85034',  // new address
            'no_customer' => '33602',  // there is no customer with this primary key (email+website)
            'new_no_address_id' => '32301'// new address without address id
        ],
        'update' => [ // this data is changed in CSV file
            '19107' => [
                'firstname' => 'Katy',
                'middlename' => 'T.',
            ],
        ],
        'remove' => [ // this data is not set in CSV file
            '19107' => [
                'city' => 'Philadelphia',
                'region' => 'Pennsylvania',
            ],
        ],
        'default' => [ // new default billing/shipping addresses
            'billing' => '85034',
            'shipping' => '19107',
        ],
    ];

    /**
     * Important data from address_import_delete.csv (postcode is key)
     *
     * @var array
     */
    protected $_deleteData = [
        'delete' => '19107',  // deleted address
        'not_delete' => '72701',  // not deleted address
    ];

    /** @var \Magento\Customer\Model\ResourceModel\Customer */
    protected $customerResource;

    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * Init new instance of address entity adapter
     */
    protected function setUp(): void
    {
        /** @var Product $productResource */
        $this->customerResource = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Model\ResourceModel\Customer::class
        );
        $this->_entityAdapter = Bootstrap::getObjectManager()->create(
            $this->_testClassName
        );
        $this->indexerProcessor = Bootstrap::getObjectManager()->create(
            Processor::class
        );
    }

    /**
     * Test _saveAddressEntity
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customer_with_addresses.php
     */
    public function testSaveAddressEntities()
    {
        // invoke _saveAddressEntities
        list($customerId, $addressId) = $this->_addTestAddress($this->_entityAdapter);

        // check DB
        $testAddress = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Address::class
        );
        $testAddress->load($addressId);
        $this->assertEquals($addressId, $testAddress->getId(), 'Incorrect address ID.');
        $this->assertEquals($customerId, $testAddress->getParentId(), 'Incorrect address customer ID.');
    }

    /**
     * Add new test address for existing customer
     *
     * @param Address $entityAdapter
     * @return array (customerID, addressID)
     */
    protected function _addTestAddress(Address $entityAdapter)
    {
        /** @var $objectManager ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        $customers = $objectManager->get(Registry::class)->registry($this->_fixtureKey);
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = reset($customers);
        $customerId = $customer->getId();

        /** @var $addressModel \Magento\Customer\Model\Address */
        $addressModel = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Address::class
        );
        $tableName = $addressModel->getResource()->getEntityTable();
        $addressId = $objectManager->get(Helper::class)
            ->getNextAutoincrement($tableName);

        $newEntityData = [
            'entity_id' => $addressId,
            'parent_id' => $customerId,
            'created_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
        ];

        // invoke _saveAddressEntities
        $saveAddressEntities = new \ReflectionMethod($this->_testClassName, '_saveAddressEntities');
        $saveAddressEntities->setAccessible(true);
        $saveAddressEntities->invoke($entityAdapter, $newEntityData, []);

        return [$customerId, $addressId];
    }

    /**
     * Test _saveAddressAttributes
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customer_with_addresses.php
     */
    public function testSaveAddressAttributes()
    {
        $this->markTestSkipped("to test _saveAddressAttributes attribute need to add custom address attribute");
        // get attributes list
        $attributesReflection = new \ReflectionProperty($this->_testClassName, '_attributes');
        $attributesReflection->setAccessible(true);
        $attributes = $attributesReflection->getValue($this->_entityAdapter);

        // get some attribute
        $attributeName = 'city';
        $this->assertArrayHasKey($attributeName, $attributes, 'Key "' . $attributeName . '" should be an attribute.');
        $attributeParams = $attributes[$attributeName];
        $this->assertArrayHasKey('id', $attributeParams, 'Attribute must have an ID.');
        $this->assertArrayHasKey('table', $attributeParams, 'Attribute must have a table.');

        // create new address with attributes
        $data = $this->_addTestAddress($this->_entityAdapter);
        $addressId = $data[1];
        $attributeId = $attributeParams['id'];
        $attributeTable = $attributeParams['table'];
        $attributeValue = 'Test City';

        $attributeArray = [];
        $attributeArray[$attributeTable][$addressId][$attributeId] = $attributeValue;

        // invoke _saveAddressAttributes
        $saveAttributes = new \ReflectionMethod($this->_testClassName, '_saveAddressAttributes');
        $saveAttributes->setAccessible(true);
        $saveAttributes->invoke($this->_entityAdapter, $attributeArray);

        // check DB
        /** @var $testAddress \Magento\Customer\Model\Address */
        $testAddress = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Address::class
        );
        $testAddress->load($addressId);
        $this->assertEquals($addressId, $testAddress->getId(), 'Incorrect address ID.');
        $this->assertEquals($attributeValue, $testAddress->getData($attributeName), 'There is no attribute value.');
    }

    /**
     * Test _saveCustomerDefaults
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customer_with_addresses.php
     */
    public function testSaveCustomerDefaults()
    {
        /** @var $objectManager ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        // get not default address
        $customers = $objectManager->get(Registry::class)->registry($this->_fixtureKey);
        /** @var $notDefaultAddress \Magento\Customer\Model\Address */
        $notDefaultAddress = null;
        /** @var $addressCustomer \Magento\Customer\Model\Customer */
        $addressCustomer = null;
        /** @var $customer \Magento\Customer\Model\Customer */
        foreach ($customers as $customer) {
            /** @var $address \Magento\Customer\Model\Address */
            foreach ($customer->getAddressesCollection() as $address) {
                if (!$customer->getDefaultBillingAddress() && !$customer->getDefaultShippingAddress()) {
                    $notDefaultAddress = $address;
                    $addressCustomer = $customer;
                    break;
                }
                if ($notDefaultAddress) {
                    break;
                }
            }
        }
        $this->assertNotNull($notDefaultAddress, 'Not default address must exists.');
        $this->assertNotNull($addressCustomer, 'Not default address customer must exists.');

        $addressId = $notDefaultAddress->getId();
        $customerId = $addressCustomer->getId();

        // set customer defaults
        $defaults = [
            $this->customerResource->getTable('customer_entity') => [
                $customerId => ['default_billing' => $addressId, 'default_shipping' => $addressId],
            ],
        ];

        // invoke _saveCustomerDefaults
        $saveDefaults = new \ReflectionMethod($this->_testClassName, '_saveCustomerDefaults');
        $saveDefaults->setAccessible(true);
        $saveDefaults->invoke($this->_entityAdapter, $defaults);

        // check DB
        /** @var $testCustomer \Magento\Customer\Model\Customer */
        $testCustomer = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Customer::class
        );
        $testCustomer->load($customerId);
        $this->assertEquals($customerId, $testCustomer->getId(), 'Customer must exists.');
        $this->assertNotNull($testCustomer->getDefaultBillingAddress(), 'Default billing address must exists.');
        $this->assertNotNull($testCustomer->getDefaultShippingAddress(), 'Default shipping address must exists.');
        $this->assertEquals(
            $addressId,
            $testCustomer->getDefaultBillingAddress()->getId(),
            'Incorrect default billing address.'
        );
        $this->assertEquals(
            $addressId,
            $testCustomer->getDefaultShippingAddress()->getId(),
            'Incorrect default shipping address.'
        );
    }

    /**
     * Test import data method with add/update behaviour
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customers_for_address_import.php
     */
    public function testImportDataAddUpdate()
    {
        // set behaviour
        $this->_entityAdapter->setParameters(
            ['behavior' => ImportModel::BEHAVIOR_ADD_UPDATE]
        );

        // set fixture CSV file
        $sourceFile = __DIR__ . '/_files/address_import_update.csv';

        /** @var $objectManager ObjectManager */
        $objectManager = Bootstrap::getObjectManager();
        $filesystem = $objectManager->create(Filesystem::class);

        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $result = $this->_entityAdapter->setSource(
            ImportAdapter::findAdapterFor($sourceFile, $directoryWrite)
        )
            ->validateData()
            ->hasToBeTerminated();
        $this->assertFalse($result, 'Validation result must be false.');

        // import data
        $this->_entityAdapter->importData();

        // form attribute list
        $keyAttribute = 'postcode';
        $requiredAttributes[] = [$keyAttribute];
        foreach (['update', 'remove'] as $action) {
            foreach ($this->_updateData[$action] as $attributes) {
                $requiredAttributes[] = array_keys($attributes);
            }
        }
        $requiredAttributes = array_merge([], ...$requiredAttributes);

        // get addresses
        $addressCollection = Bootstrap::getObjectManager()->create(
            Collection::class
        );
        $addressCollection->addAttributeToSelect($requiredAttributes);
        $addresses = [];
        /** @var $address \Magento\Customer\Model\Address */
        foreach ($addressCollection as $address) {
            $addresses[$address->getData($keyAttribute)] = $address;
        }

        // is addresses exists
        $this->assertArrayHasKey($this->_updateData['address']['update'], $addresses, 'Address must exist.');
        $this->assertArrayHasKey($this->_updateData['address']['new'], $addresses, 'Address must exist.');
        $this->assertArrayNotHasKey(
            $this->_updateData['address']['no_customer'],
            $addresses,
            'Address must not exist.'
        );
        $this->assertArrayHasKey(
            $this->_updateData['address']['new_no_address_id'],
            $addresses,
            'Address must exist.'
        );

        // are updated address fields have new values
        $updatedAddressId = $this->_updateData['address']['update'];
        /** @var $updatedAddress \Magento\Customer\Model\Address */
        $updatedAddress = $addresses[$updatedAddressId];
        $updatedData = $this->_updateData['update'][$updatedAddressId];
        foreach ($updatedData as $fieldName => $fieldValue) {
            $this->assertEquals($fieldValue, $updatedAddress->getData($fieldName));
        }

        // are removed data fields have old values
        $removedData = $this->_updateData['remove'][$updatedAddressId];
        foreach ($removedData as $fieldName => $fieldValue) {
            $this->assertEquals($fieldValue, $updatedAddress->getData($fieldName));
        }

        // are default billing/shipping addresses have new value
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Customer::class
        );
        $customer->setWebsiteId(0);
        $customer->loadByEmail('BetsyParker@example.com');
        $defaultsData = $this->_updateData['default'];
        $this->assertEquals(
            $defaultsData['billing'],
            $customer->getDefaultBillingAddress()->getData($keyAttribute),
            'Incorrect default billing address'
        );
        $this->assertEquals(
            $defaultsData['shipping'],
            $customer->getDefaultShippingAddress()->getData($keyAttribute),
            'Incorrect default shipping address'
        );
    }

    /**
     * Test import data method with delete behaviour
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customers_for_address_import.php
     */
    public function testImportDataDelete()
    {
        // set behaviour
        $this->_entityAdapter->setParameters(['behavior' => ImportModel::BEHAVIOR_DELETE]);

        // set fixture CSV file
        $sourceFile = __DIR__ . '/_files/address_import_delete.csv';

        /** @var $objectManager ObjectManager */
        $objectManager = Bootstrap::getObjectManager();
        $filesystem = $objectManager->create(Filesystem::class);
        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $result = $this->_entityAdapter->setSource(
            ImportAdapter::findAdapterFor($sourceFile, $directoryWrite)
        )->validateData()->hasToBeTerminated();
        $this->assertTrue(!$result, 'Validation result must be true.');

        // import data
        $this->_entityAdapter->importData();

        // key attribute
        $keyAttribute = 'postcode';

        // get addresses
        /** @var $addressCollection Collection */
        $addressCollection = Bootstrap::getObjectManager()->create(
            Collection::class
        );
        $addressCollection->addAttributeToSelect($keyAttribute);
        $addresses = [];
        /** @var $address \Magento\Customer\Model\Address */
        foreach ($addressCollection as $address) {
            $addresses[$address->getData($keyAttribute)] = $address;
        }

        // is addresses exists
        $this->assertArrayNotHasKey($this->_deleteData['delete'], $addresses, 'Address must not exist.');
        $this->assertArrayHasKey($this->_deleteData['not_delete'], $addresses, 'Address must exist.');
    }

    /**
     * Case when attribute settings for two websites are different.
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/CustomerImportExport/_files/two_addresses.php
     * @return void
     */
    public function testDifferentOptions(): void
    {
        /** @var $objectManager ObjectManager */
        $objectManager = Bootstrap::getObjectManager();
        /** @var Filesystem $filesystem */
        $filesystem = $objectManager->create(Filesystem::class);

        //Only add/update behaviour will have validation in place required to
        //test this case
        $this->_entityAdapter->setParameters(
            ['behavior' => ImportModel::BEHAVIOR_ADD_UPDATE]
        );
        //Load from
        $sourceFile = __DIR__ . '/_files/two_addresses_import_update.csv';
        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        //Validate
        $adapter = ImportAdapter::findAdapterFor($sourceFile, $directoryWrite);
        $errors = $this->_entityAdapter->setSource($adapter)->validateData();
        $this->assertEmpty($errors->getErrorsCount(), 'CSV must be valid');
        //Import
        $imported = $this->_entityAdapter->importData();
        $this->assertTrue($imported, 'Must be successfully imported');
    }

    /**
     * Test customer indexer gets invalidated after import when Update on Schedule mode is set
     *
     * @magentoDbIsolation enabled
     */
    public function testCustomerIndexer(): void
    {
        $file = __DIR__ . '/_files/address_import_update.csv';
        $filesystem = Bootstrap::getObjectManager()
            ->create(Filesystem::class);
        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = new Csv($file, $directoryWrite);
        $this->_entityAdapter
            ->setParameters(['behavior' => ImportModel::BEHAVIOR_ADD_UPDATE])
            ->setSource($source)
            ->validateData()
            ->hasToBeTerminated();
        $this->indexerProcessor->getIndexer()->reindexAll();
        $statusBeforeImport = $this->indexerProcessor->getIndexer()->getStatus();
        $this->indexerProcessor->getIndexer()->setScheduled(true);
        $this->_entityAdapter->importData();
        $statusAfterImport = $this->indexerProcessor->getIndexer()->getStatus();
        $this->assertEquals(StateInterface::STATUS_VALID, $statusBeforeImport);
        $this->assertEquals(StateInterface::STATUS_INVALID, $statusAfterImport);
    }

    /**
     * Test import address with region for a country that does not have regions defined
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/import_export/customer_with_addresses.php
     */
    public function testImportAddressWithOptionalRegion()
    {
        $customer = $this->getCustomer('BetsyParker@example.com');
        $file = __DIR__ . '/_files/import_uk_address.csv';
        $errors = $this->doImport($file);
        $this->assertImportValidationPassed($errors);
        $address = $this->getAddresses(
            [
                'parent_id' => $customer->getId(),
                'country_id' => 'GB',
            ]
        );
        $this->assertCount(1, $address);
        $this->assertNull($address[0]->getRegionId());
        $this->assertEquals('Liverpool', $address[0]->getRegion()->getRegion());
    }

    /**
     * Test update first name and last name
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/import_export/customer_with_addresses.php
     */
    public function testUpdateFirstAndLastName()
    {
        $customer = $this->getCustomer('BetsyParker@example.com');
        $addresses = $this->getAddresses(
            [
                'parent_id' => $customer->getId(),
            ]
        );
        $this->assertCount(1, $addresses);
        $address = $addresses[0];
        $row = [
            '_website' => 'base',
            '_email' => $customer->getEmail(),
            '_entity_id' => $address->getId(),
            'firstname' => 'Mark',
            'lastname' => 'Antony',
        ];
        $file = $this->generateImportFile([$row]);
        $errors = $this->doImport($file);
        $this->assertImportValidationPassed($errors);
        $objectManager = Bootstrap::getObjectManager();
        //clear cache
        $objectManager->get(AddressRegistry::class)->remove($address->getId());
        $addresses = $this->getAddresses(
            [
                'parent_id' => $customer->getId(),
                'entity_id' => $address->getId(),
            ]
        );
        $this->assertCount(1, $addresses);
        $updatedAddress = $addresses[0];
        //assert that firstname and lastname were updated
        $this->assertEquals($row['firstname'], $updatedAddress->getFirstname());
        $this->assertEquals($row['lastname'], $updatedAddress->getLastname());
        //assert other values have not changed
        $this->assertEquals($address->getStreet(), $updatedAddress->getStreet());
        $this->assertEquals($address->getCity(), $updatedAddress->getCity());
        $this->assertEquals($address->getCountryId(), $updatedAddress->getCountryId());
        $this->assertEquals($address->getPostcode(), $updatedAddress->getPostcode());
        $this->assertEquals($address->getTelephone(), $updatedAddress->getTelephone());
        $this->assertEquals($address->getRegionId(), $updatedAddress->getRegionId());
    }

    /**
     * Get Addresses by filter
     *
     * @param array $filter
     * @return AddressInterface[]
     */
    private function getAddresses(array $filter): array
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var AddressRepositoryInterface $repository */
        $repository = $objectManager->create(AddressRepositoryInterface::class);
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        foreach ($filter as $attr => $value) {
            $searchCriteriaBuilder->addFilter($attr, $value);
        }
        return $repository->getList($searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @param string $email
     * @return CustomerInterface
     */
    private function getCustomer(string $email): CustomerInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        $customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        return $customerRepository->get($email);
    }

    /**
     * @param string $file
     * @param string $behavior
     * @param bool $validateOnly
     * @return ProcessingErrorAggregatorInterface
     */
    private function doImport(
        string $file,
        string $behavior = ImportModel::BEHAVIOR_ADD_UPDATE,
        bool $validateOnly = false
    ): ProcessingErrorAggregatorInterface {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Filesystem $filesystem */
        $filesystem = $objectManager->create(Filesystem::class);
        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = ImportAdapter::findAdapterFor($file, $directoryWrite);
        $errors = $this->_entityAdapter
            ->setParameters(['behavior' => $behavior])
            ->setSource($source)
            ->validateData();
        if (!$validateOnly && !$errors->getAllErrors()) {
            $this->_entityAdapter->importData();
        }
        return $errors;
    }

    /**
     * @param array $data
     * @return string
     */
    private function generateImportFile(array $data): string
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Filesystem $filesystem */
        $filesystem = $objectManager->get(Filesystem::class);
        $tmpDir = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmpFilename = uniqid('test_import_address_') . '.csv';
        $stream = $tmpDir->openFile($tmpFilename, 'w+');
        $stream->lock();
        $stream->writeCsv($this->getFields());
        $emptyRow = array_fill_keys($this->getFields(), '');
        foreach ($data as $row) {
            $row = array_replace($emptyRow, $row);
            $stream->writeCsv($row);
        }
        $stream->unlock();
        $stream->close();
        return $tmpDir->getAbsolutePath($tmpFilename);
    }

    /**
     * @param ProcessingErrorAggregatorInterface $errors
     */
    private function assertImportValidationPassed(ProcessingErrorAggregatorInterface $errors): void
    {
        if ($errors->getAllErrors()) {
            $messages = [];
            $messages[] = 'Import validation failed';
            $messages[] = '';
            foreach ($errors->getAllErrors() as $error) {
                $messages[] = sprintf(
                    '%s: #%d [%s] %s: %s',
                    strtoupper($error->getErrorLevel()),
                    $error->getRowNumber(),
                    $error->getErrorCode(),
                    $error->getErrorMessage(),
                    $error->getErrorDescription()
                );
            }
            $this->fail(implode("\n", $messages));
        }
    }

    /**
     * @return array
     */
    private function getFields(): array
    {
        return [
            '_website',
            '_email',
            '_entity_id',
            'city',
            'company',
            'country_id',
            'fax',
            'firstname',
            'lastname',
            'middlename',
            'postcode',
            'prefix',
            'region',
            'region_id',
            'street',
            'suffix',
            'telephone',
            'vat_id',
            'vat_is_valid',
            'vat_request_date',
            'vat_request_id',
            'vat_request_success',
            '_address_default_billing_',
            '_address_default_shipping_',
        ];
    }
}
