<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\CustomerImportExport\Model\Import\Address
 */
namespace Magento\CustomerImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import as ImportModel;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Indexer\StateInterface;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tested class name
     *
     * @var string
     */
    protected $_testClassName = \Magento\CustomerImportExport\Model\Import\Address::class;

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
     * @var \Magento\Customer\Model\Indexer\Processor
     */
    private $indexerProcessor;

    /**
     * Init new instance of address entity adapter
     */
    protected function setUp(): void
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product $productResource */
        $this->customerResource = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Model\ResourceModel\Customer::class
        );
        $this->_entityAdapter = Bootstrap::getObjectManager()->create(
            $this->_testClassName
        );
        $this->indexerProcessor = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Indexer\Processor::class
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
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        $customers = $objectManager->get(\Magento\Framework\Registry::class)->registry($this->_fixtureKey);
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = reset($customers);
        $customerId = $customer->getId();

        /** @var $addressModel \Magento\Customer\Model\Address */
        $addressModel = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Address::class
        );
        $tableName = $addressModel->getResource()->getEntityTable();
        $addressId = $objectManager->get(\Magento\ImportExport\Model\ResourceModel\Helper::class)
            ->getNextAutoincrement($tableName);

        $newEntityData = [
            'entity_id' => $addressId,
            'parent_id' => $customerId,
            'created_at' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
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
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        // get not default address
        $customers = $objectManager->get(\Magento\Framework\Registry::class)->registry($this->_fixtureKey);
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

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
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
        $requiredAttributes[] = $keyAttribute;
        foreach (['update', 'remove'] as $action) {
            foreach ($this->_updateData[$action] as $attributes) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $requiredAttributes = array_merge($requiredAttributes, array_keys($attributes));
            }
        }

        // get addresses
        $addressCollection = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Address\Collection::class
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

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
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
        /** @var $addressCollection \Magento\Customer\Model\ResourceModel\Address\Collection */
        $addressCollection = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Address\Collection::class
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
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
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
        $filesystem = Bootstrap::getObjectManager()->create(Filesystem::class);
        $directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = new \Magento\ImportExport\Model\Import\Source\Csv($file, $directoryWrite);
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
}
