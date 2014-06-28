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

class CustomerCompositeTest extends \PHPUnit_Framework_TestCase
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
    protected $_customerAttributes = array(self::ATTRIBUTE_CODE_FIRST_NAME, self::ATTRIBUTE_CODE_LAST_NAME);

    /**
     * Customers and addresses before import, address ID is postcode
     *
     * @var array
     */
    protected $_beforeImport = array(
        'betsyparker@example.com' => array(
            'addresses' => array('19107', '72701'),
            'data' => array(self::ATTRIBUTE_CODE_FIRST_NAME => 'Betsy', self::ATTRIBUTE_CODE_LAST_NAME => 'Parker')
        )
    );

    /**
     * Customers and addresses after import, address ID is postcode
     *
     * @var array
     */
    protected $_afterImport = array(
        'betsyparker@example.com' => array(
            'addresses' => array('19107', '72701', '19108'),
            'data' => array(
                self::ATTRIBUTE_CODE_FIRST_NAME => 'NotBetsy',
                self::ATTRIBUTE_CODE_LAST_NAME => 'NotParker'
            )
        ),
        'anthonyanealy@magento.com' => array('addresses' => array('72701', '92664')),
        'loribbanks@magento.com' => array('addresses' => array('98801')),
        'kellynilson@magento.com' => array('addresses' => array())
    );

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_entityAdapter = $this->_objectManager->create(
            'Magento\CustomerImportExport\Model\Import\CustomerComposite'
        );
    }

    /**
     * Assertion of current customer and address data
     *
     * @param array $expectedData
     */
    protected function _assertCustomerData(array $expectedData)
    {
        /** @var $collection \Magento\Customer\Model\Resource\Customer\Collection */
        $collection = $this->_objectManager->create('Magento\Customer\Model\Resource\Customer\Collection');
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
    public function testImportData($behavior, $sourceFile, array $dataBefore, array $dataAfter, array $errors = array())
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        // set entity adapter parameters
        $this->_entityAdapter->setParameters(array('behavior' => $behavior));
        /** @var \Magento\Framework\App\Filesystem $filesystem */
        $filesystem = $this->_objectManager->create('Magento\Framework\App\Filesystem');
        $rootDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::ROOT_DIR);

        // set fixture CSV file
        $result = $this->_entityAdapter->setSource(
            \Magento\ImportExport\Model\Import\Adapter::findAdapterFor($sourceFile, $rootDirectory)
        )->isDataValid();
        if ($errors) {
            $this->assertFalse($result);
        } else {
            $this->assertTrue($result);
        }

        // assert validation errors
        // can't use error codes because entity adapter gathers only error messages from aggregated adapters
        $actualErrors = array_values($this->_entityAdapter->getErrorMessages());
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
        $sourceData = array(
            'delete_behavior' => array(
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                '$sourceFile' => $filesDirectory . self::DELETE_FILE_NAME,
                '$dataBefore' => $this->_beforeImport,
                '$dataAfter' => array()
            )
        );

        $sourceData['add_update_behavior'] = array(
            '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            '$sourceFile' => $filesDirectory . self::UPDATE_FILE_NAME,
            '$dataBefore' => $this->_beforeImport,
            '$dataAfter' => $this->_afterImport,
            '$errors' => array(array(6)) // row #6 has no website
        );

        return $sourceData;
    }
}
