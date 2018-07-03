<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class CustomerCompositeTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Attributes used in test assertions
     */
    const ATTRIBUTE_CODE_FIRST_NAME = 'firstname';

    const ATTRIBUTE_CODE_LAST_NAME = 'lastname';

    /**#@-*/

    /**#@+
     * Source *.csv file names for different behaviors
     */
    const UPDATE_FILE_NAME = 'customer_composite_update.csv';

    const DELETE_FILE_NAME = 'customer_composite_delete.csv';

    /**#@-*/

    /**
     * Object Manager instance
     *
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * Composite customer entity adapter instance
     *
     * @var CustomerComposite
     */
    protected $_entityAdapter;

    /**
     * Additional customer attributes for assertion
     *
     * @var array
     */
    protected $_customerAttributes = [self::ATTRIBUTE_CODE_FIRST_NAME, self::ATTRIBUTE_CODE_LAST_NAME];

    /**
     * Customers and addresses before import, address ID is postcode
     *
     * @var array
     */
    protected $_beforeImport = [
        'betsyparker@example.com' => [
            'addresses' => ['19107', '72701'],
            'data' => [self::ATTRIBUTE_CODE_FIRST_NAME => 'Betsy', self::ATTRIBUTE_CODE_LAST_NAME => 'Parker'],
        ],
    ];

    /**
     * Customers and addresses after import, address ID is postcode
     *
     * @var array
     */
    protected $_afterImport = [
        'betsyparker@example.com' => [
            'addresses' => ['19107', '72701', '19108'],
            'data' => [
                self::ATTRIBUTE_CODE_FIRST_NAME => 'NotBetsy',
                self::ATTRIBUTE_CODE_LAST_NAME => 'NotParker',
            ],
        ],
        'anthonyanealy@magento.com' => ['addresses' => ['72701', '92664']],
        'loribbanks@magento.com' => ['addresses' => ['98801']],
        'kellynilson@magento.com' => ['addresses' => []],
    ];

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_entityAdapter = $this->_objectManager->create(
            \Magento\CustomerImportExport\Model\Import\CustomerComposite::class
        );
    }

    /**
     * Assertion of current customer and address data
     *
     * @param array $expectedData
     */
    protected function _assertCustomerData(array $expectedData)
    {
        /** @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $collection = $this->_objectManager->create(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);
        $collection->addAttributeToSelect($this->_customerAttributes);
        $customers = $collection->getItems();

        $this->assertSameSize($expectedData, $customers);

        /** @var $customer \Magento\Customer\Model\Customer */
        foreach ($customers as $customer) {
            // assert customer existence
            $email = strtolower($customer->getEmail());
            $this->assertArrayHasKey($email, $expectedData);

            // assert customer data (only for required customers)
            if (isset($expectedData[$email]['data'])) {
                foreach ($expectedData[$email]['data'] as $attribute => $expectedValue) {
                    $this->assertEquals($expectedValue, $customer->getData($attribute));
                }
            }

            // assert address data
            $addresses = $customer->getAddresses();
            $this->assertSameSize($expectedData[$email]['addresses'], $addresses);
            /** @var $address \Magento\Customer\Model\Address */
            foreach ($addresses as $address) {
                $this->assertContains($address->getData('postcode'), $expectedData[$email]['addresses']);
            }
        }
    }

    /**
     * @param string $behavior
     * @param string $sourceFile
     * @param array $dataBefore
     * @param array $dataAfter
     * @param array $errors
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customers_for_address_import.php
     * @magentoAppIsolation enabled
     *
     * @dataProvider importDataDataProvider
     */
    public function testImportData($behavior, $sourceFile, array $dataBefore, array $dataAfter, array $errors = [])
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        // set entity adapter parameters
        $this->_entityAdapter->setParameters(['behavior' => $behavior]);
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->_objectManager->create(\Magento\Framework\Filesystem::class);
        $rootDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $this->_entityAdapter->getErrorAggregator()->initValidationStrategy(
            ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR,
            10
        );

        // set fixture CSV file
        $result = $this->_entityAdapter->setSource(
            \Magento\ImportExport\Model\Import\Adapter::findAdapterFor($sourceFile, $rootDirectory)
        )
            ->validateData()
            ->hasToBeTerminated();
        if ($errors) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }

        // assert validation errors
        // can't use error codes because entity adapter gathers only error messages from aggregated adapters
        $actualErrors = array_values($this->_entityAdapter->getErrorAggregator()->getRowsGroupedByErrorCode());
        $this->assertEquals($errors, $actualErrors);

        // assert data before import
        $this->_assertCustomerData($dataBefore);

        // import data
        $this->_entityAdapter->importData();

        // assert data after import
        $this->_assertCustomerData($dataAfter);
    }

    /**
     * Data provider for testImportData
     *
     * @return array
     */
    public function importDataDataProvider()
    {
        $filesDirectory = __DIR__ . '/_files/';
        $sourceData = [
            'delete_behavior' => [
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$sourceFile' => $filesDirectory . self::DELETE_FILE_NAME,
                '$dataBefore' => $this->_beforeImport,
                '$dataAfter' => [],
            ],
        ];

        $sourceData['add_update_behavior'] = [
            '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            '$sourceFile' => $filesDirectory . self::UPDATE_FILE_NAME,
            '$dataBefore' => $this->_beforeImport,
            '$dataAfter' => $this->_afterImport,
            '$errors' => [],
        ];

        return $sourceData;
    }
}
