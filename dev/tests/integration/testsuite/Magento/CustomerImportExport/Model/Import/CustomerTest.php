<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\CustomerImportExport\Model\Import;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Indexer\Processor;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Directory\Write as DirectoryWrite;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Source\CsvFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for class Customer which covers validation logic
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Model object which used for tests
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Model object which used for tests
     *
     * @var Customer&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_customerData;

    /**
     * @var DirectoryWrite
     */
    protected $directoryWrite;

    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * @var CsvFactory
     */
    private $csvFactory;

    /**
     * @var WriteFactory
     */
    private $writeFactory;
    /**
     * Create all necessary data for tests
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->_model = $this->objectManager->create(Customer::class);
        $this->_model->setParameters(['behavior' => Import::BEHAVIOR_ADD_UPDATE]);
        $this->indexerProcessor = $this->objectManager->create(\Magento\Customer\Model\Indexer\Processor::class);
        $propertyAccessor = new \ReflectionProperty($this->_model, 'errorMessageTemplates');
        $propertyAccessor->setAccessible(true);
        $propertyAccessor->setValue($this->_model, []);
        $this->_customerData = [
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'group_id' => 1,
            Customer::COLUMN_EMAIL => 'customer@example.com',
            Customer::COLUMN_WEBSITE => 'base',
            Customer::COLUMN_STORE => 'default',
            'store_id' => 1,
            'website_id' => 1,
            'password' => 'password',
        ];
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $this->directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->writeFactory = $this->objectManager->get(WriteFactory::class);
        $this->csvFactory = $this->objectManager->get(CsvFactory::class);
    }

    /**
     * Test importData() method
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customer.php
     */
    public function testImportData()
    {
        // 3 customers will be imported.
        // 1 of this customers is already exist, but its first and last name were changed in file
        $expectAddedCustomers = 5;

        $source = new \Magento\ImportExport\Model\Import\Source\Csv(
            __DIR__ . '/_files/customers_with_gender_to_import.csv',
            $this->directoryWrite
        );

        $existingCustomer = $this->getCustomer('CharlesTAlston@teleworm.us', 1);

        /** @var $customersCollection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $customersCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Customer\Collection::class
        );
        $customersCollection->addAttributeToSelect('firstname', 'inner')->addAttributeToSelect('lastname', 'inner');

        $existCustomersCount = count($customersCollection->load());

        $customersCollection->resetData();
        $customersCollection->clear();

        $this->_model
            ->setParameters(['behavior' => Import::BEHAVIOR_ADD_UPDATE])
            ->setSource($source)
            ->validateData()
            ->hasToBeTerminated();

        $this->_model->importData();

        $customers = $customersCollection->getItems();

        $addedCustomers = count($customers) - $existCustomersCount;

        $this->assertEquals($expectAddedCustomers, $addedCustomers, 'Added unexpected amount of customers');

        $updatedCustomer = $customers[$existingCustomer->getId()];

        $this->assertNotEquals(
            $existingCustomer->getFirstname(),
            $updatedCustomer->getFirstname(),
            'Firstname must be changed'
        );

        $this->assertNotEquals(
            $existingCustomer->getLastname(),
            $updatedCustomer->getLastname(),
            'Lastname must be changed'
        );

        $this->assertNotEquals(
            $existingCustomer->getCreatedAt(),
            $updatedCustomer->getCreatedAt(),
            'Creation date must be changed'
        );
        $this->assertNotEquals(
            $existingCustomer->getDisableAutoGroupChange(),
            $updatedCustomer->getDisableAutoGroupChange(),
            'Disable automatic group change based on VAT ID must be changed'
        );
        $this->assertEquals(
            $existingCustomer->getGender(),
            $updatedCustomer->getGender(),
            'Gender must be changed'
        );
    }

    /**
     * Decompresses if gz compressed, stores in memory or temp file, and loads CSV adapter
     *
     * @param string $importData
     * @return Import\AbstractSource
     */
    private function createImportAdapter(string $importData)
    {
        if (0 === strncmp("\x1f\x8b", $importData, 2)) { // gz's magic string
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $importData = gzdecode($importData);
        }
        $openedFile = $this->writeFactory->create('php://temp', '', 'w');
        $openedFile->write($importData);
        unset($importData);
        $directory = $this->directoryWrite;
        $adapter = $this->csvFactory->create(['directory' => $directory, 'file' => $openedFile]);
        return $adapter;
    }

    /**
     * Test validateSource() and importData() and using same $ids between them
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customer.php
     */
    public function testValidateSourceAndImportSource()
    {
        /** @var Import $import */
        $import = $this->objectManager->create(Import::class);
        $importData = \file_get_contents(__DIR__ . '/_files/2k_customers.csv.gz');
        $source = $this->createImportAdapter($importData);
        unset($importData);
        $import->setData([
            'form_key' => 'Ded3z8XBEaMWt3sH',
            'entity' => 'customer',
            'behavior' => 'add_update',
            'validation_strategy' => 'validation-stop-on-errors',
            'allowed_error_count' => '10',
            '_import_field_separator' => ',',
            '_import_multiple_value_separator' => ',',
            '_import_empty_attribute_value_constant' => '__EMPTY__VALUE__',
            'import_images_file_dir' => '',
            '_import_ids' => '',
        ]);
        $import->validateSource($source);
        $ids = $import->getValidatedIds();
        $errorAggregator = $import->getErrorAggregator();
        $errorStrings = [];
        foreach ($errorAggregator->getAllErrors() as $error) {
            $errorStrings[] = sprintf(
                "Error:\nRowNumber: %s\nColumnName: %s\nCode: %s\nDescription: %s\nLevel: %s\nMessage: %s\n",
                (string)$error->getRowNumber(),
                $error->getColumnName(),
                $error->getErrorCode(),
                $error->getErrorDescription(),
                $error->getErrorLevel(),
                $error->getErrorMessage(),
            );
        }
        if (!empty($errorStrings)) {
            $exceptionString = sprintf(
                "Errors:\n%s\n",
                implode("\n", $errorStrings)
            );
            throw new \Exception($exceptionString);
        }
        $this->assertCount(20, $ids);
        /** @var Import $import2 */
        $import2 = $this->objectManager->create(Import::class);
        $import2->setData([
            'form_key' => 'DedGz8CNEaMWt3sH',
            'entity' => 'customer',
            'behavior' => 'add_update',
            'validation_strategy' => 'validation-stop-on-errors',
            'allowed_error_count' => '10',
            '_import_field_separator' => ',',
            '_import_multiple_value_separator' => ',',
            '_import_empty_attribute_value_constant' => '__EMPTY__VALUE__',
            'import_images_file_dir' => '',
            '_import_ids' => implode(',', $ids),
        ]);
        $this->assertEmpty($import2->getValidatedIds());
        $import2->importSource();
        $createdItemsCount = $import2->getCreatedItemsCount();
        $this->assertEquals(2000, $createdItemsCount);
    }

    /**
     * Tests importData() method.
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customer.php
     *
     * @return void
     */
    public function testImportDataWithOneAdditionalColumn(): void
    {
        $source = new \Magento\ImportExport\Model\Import\Source\Csv(
            __DIR__ . '/_files/customer_to_import_with_one_additional_column.csv',
            $this->directoryWrite
        );

        $existingCustomer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Customer::class
        );
        $existingCustomer->setWebsiteId(1);
        $existingCustomer = $existingCustomer->loadByEmail('CharlesTAlston@teleworm.us');

        /** @var $customersCollection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $customersCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Customer\Collection::class
        );
        $customersCollection->resetData();
        $customersCollection->clear();

        $this->_model
            ->setParameters(['behavior' => Import::BEHAVIOR_ADD_UPDATE])
            ->setSource($source)
            ->validateData()
            ->hasToBeTerminated();
        sleep(1);
        $this->_model->importData();

        $customers = $customersCollection->getItems();

        $updatedCustomer = $customers[$existingCustomer->getId()];

        $this->assertNotEquals(
            $existingCustomer->getFirstname(),
            $updatedCustomer->getFirstname(),
            'Firstname must be changed'
        );

        $this->assertNotEquals(
            $existingCustomer->getUpdatedAt(),
            $updatedCustomer->getUpdatedAt(),
            'Updated at date must be changed'
        );

        $this->assertEquals(
            $existingCustomer->getLastname(),
            $updatedCustomer->getLastname(),
            'Lastname must not be changed'
        );

        $this->assertEquals(
            $existingCustomer->getStoreId(),
            $updatedCustomer->getStoreId(),
            'Store Id must not be changed'
        );

        $this->assertEquals(
            $existingCustomer->getCreatedAt(),
            $updatedCustomer->getCreatedAt(),
            'Creation date must not be changed'
        );

        $this->assertEquals(
            $existingCustomer->getGroupId(),
            $updatedCustomer->getGroupId(),
            'Customer group must not be changed'
        );
    }

    /**
     * Test importData() method (delete behavior)
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customers.php
     */
    public function testDeleteData()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $source = new \Magento\ImportExport\Model\Import\Source\Csv(
            __DIR__ . '/_files/customers_to_delete.csv',
            $this->directoryWrite
        );

        /** @var $customerCollection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $customerCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Customer\Collection::class
        );
        $this->assertEquals(3, $customerCollection->count(), 'Count of existing customers are invalid');

        $this->_model->setParameters(['behavior' => Import::BEHAVIOR_DELETE])
            ->setSource($source)
            ->validateData();

        $this->_model->importData();

        $customerCollection->resetData();
        $customerCollection->clear();
        $this->assertEmpty($customerCollection->count(), 'Customers were not imported');
    }

    public function testGetEntityTypeCode()
    {
        $this->assertEquals('customer', $this->_model->getEntityTypeCode());
    }

    public function testValidateRowDuplicateEmail()
    {
        $this->_model->getErrorAggregator()->clear();
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(0, $this->_model->getErrorAggregator()->getErrorsCount());

        $this->_customerData[Customer::COLUMN_EMAIL] = strtoupper(
            $this->_customerData[Customer::COLUMN_EMAIL]
        );
        $this->_model->validateRow($this->_customerData, 1);
        $this->assertEquals(1, $this->_model->getErrorAggregator()->getErrorsCount());
        $this->assertNotEmpty(
            $this->_model->getErrorAggregator()->getErrorsByCode([Customer::ERROR_DUPLICATE_EMAIL_SITE])
        );
    }

    public function testValidateRowInvalidEmail()
    {
        $this->_model->getErrorAggregator()->clear();
        $this->_customerData[Customer::COLUMN_EMAIL] = 'wrong_email@format';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(1, $this->_model->getErrorAggregator()->getErrorsCount());
        $this->assertNotEmpty(
            $this->_model->getErrorAggregator()->getErrorsByCode([Customer::ERROR_INVALID_EMAIL])
        );
    }

    public function testValidateRowInvalidWebsite()
    {
        $this->_model->getErrorAggregator()->clear();
        $this->_customerData[Customer::COLUMN_WEBSITE] = 'not_existing_web_site';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(1, $this->_model->getErrorAggregator()->getErrorsCount());
        $this->assertNotEmpty(
            $this->_model->getErrorAggregator()->getErrorsByCode([Customer::ERROR_INVALID_WEBSITE])
        );
    }

    public function testValidateRowInvalidStore()
    {
        $this->_model->getErrorAggregator()->clear();
        $this->_customerData[Customer::COLUMN_STORE] = 'not_existing_web_store';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(1, $this->_model->getErrorAggregator()->getErrorsCount());
        $this->assertNotEmpty(
            $this->_model->getErrorAggregator()->getErrorsByCode([Customer::ERROR_INVALID_STORE])
        );
    }

    public function testValidateRowPasswordLengthIncorrect()
    {
        $this->_model->getErrorAggregator()->clear();
        $this->_customerData['password'] = '12345';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(1, $this->_model->getErrorAggregator()->getErrorsCount());
        $this->assertNotEmpty(
            $this->_model->getErrorAggregator()->getErrorsByCode([Customer::ERROR_PASSWORD_LENGTH])
        );
    }

    public function testValidateRowPasswordLengthCorrect()
    {
        $this->_model->getErrorAggregator()->clear();
        $this->_customerData['password'] = '1234567890';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(0, $this->_model->getErrorAggregator()->getErrorsCount());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/import_export/customers.php
     */
    public function testValidateRowAttributeRequired()
    {
        $this->_model->getErrorAggregator()->clear();
        unset($this->_customerData['firstname']);
        unset($this->_customerData['lastname']);
        unset($this->_customerData['group_id']);

        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(0, $this->_model->getErrorAggregator()->getErrorsCount());

        $this->_customerData[Customer::COLUMN_EMAIL] = 'new.customer@example.com';
        $this->_model->validateRow($this->_customerData, 1);
        $this->assertGreaterThan(0, $this->_model->getErrorAggregator()->getErrorsCount());
        $this->assertNotEmpty(
            $this->_model->getErrorAggregator()->getErrorsByCode([Customer::ERROR_VALUE_IS_REQUIRED])
        );
    }

    public function testValidateEmailForDeleteBehavior()
    {
        $this->_model->getErrorAggregator()->clear();
        $this->_customerData[Customer::COLUMN_EMAIL] = 'new.customer@example.com';
        $this->_model->setParameters(['behavior' => Import::BEHAVIOR_DELETE]);
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertGreaterThan(0, $this->_model->getErrorAggregator()->getErrorsCount());
        $this->assertNotEmpty(
            $this->_model->getErrorAggregator()->getErrorsByCode([Customer::ERROR_CUSTOMER_NOT_FOUND])
        );
    }

    /**
     * Test import existing customers
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customers.php
     * @return void
     */
    public function testUpdateExistingCustomers(): void
    {
        $this->doImport(__DIR__ . '/_files/customers_to_update.csv', Import::BEHAVIOR_ADD_UPDATE);
        $customer = $this->getCustomer('customer@example.com', 1);
        $this->assertEquals('Firstname-updated', $customer->getFirstname());
        $this->assertEquals('Lastname-updated', $customer->getLastname());
        $this->assertEquals(1, $customer->getStoreId());
        $customer = $this->getCustomer('julie.worrell@example.com', 1);
        $this->assertEquals('Julie-updated', $customer->getFirstname());
        $this->assertEquals('Worrell-updated', $customer->getLastname());
        $this->assertEquals(1, $customer->getStoreId());
        $customer = $this->getCustomer('david.lamar@example.com', 1);
        $this->assertEquals('David-updated', $customer->getFirstname());
        $this->assertEquals('Lamar-updated', $customer->getLastname());
        $this->assertEquals(1, $customer->getStoreId());
    }

    /**
     * Test customer indexer gets invalidated after import when Update on Schedule mode is set
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testCustomerIndexer(): void
    {
        $this->indexerProcessor->getIndexer()->reindexAll();
        $statusBeforeImport = $this->indexerProcessor->getIndexer()->getStatus();
        $this->indexerProcessor->getIndexer()->setScheduled(true);
        $this->doImport(__DIR__ . '/_files/customers_with_gender_to_import.csv', Import::BEHAVIOR_ADD_UPDATE);
        $statusAfterImport = $this->indexerProcessor->getIndexer()->getStatus();
        $this->assertEquals(StateInterface::STATUS_VALID, $statusBeforeImport);
        $this->assertEquals(StateInterface::STATUS_INVALID, $statusAfterImport);
    }

    /**
     * Gets customer entity.
     *
     * @param string $email
     * @param int $websiteId
     * @return CustomerInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomer(string $email, int $websiteId): CustomerInterface
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var CustomerRepositoryInterface $repository */
        $repository = $objectManager->get(CustomerRepositoryInterface::class);
        return $repository->get($email, $websiteId);
    }

    /**
     * Import using given file and behavior
     *
     * @param string $file
     * @param string $behavior
     */
    private function doImport(string $file, string $behavior): void
    {
        $source = new \Magento\ImportExport\Model\Import\Source\Csv($file, $this->directoryWrite);
        $this->_model
            ->setParameters(['behavior' => $behavior])
            ->setSource($source)
            ->validateData()
            ->hasToBeTerminated();
        $this->_model->importData();
    }
}
