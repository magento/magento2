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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_ImportExport_Model_Import_Entity_CustomerComposite
 */
class Mage_ImportExport_Model_Import_Entity_CustomerCompositeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Composite customer entity adapter instance
     *
     * @var Mage_ImportExport_Model_Import_Entity_CustomerComposite
     */
    protected $_entityAdapter;

    /**
     * Additional customer attributes for assertion
     *
     * @var array
     */
    protected $_customerAttributes = array('firstname', 'lastname');

    /**
     * Customers and addresses before import, address ID is postcode
     *
     * @var array
     */
    protected $_beforeImport = array(
        'betsyparker@example.com' => array(
            'addresses' => array('19107', '72701'),
            'data' => array(
                'firstname' => 'Betsy',
                'lastname'  => 'Parker',
            ),
        ),
    );

    /**
     * Customers and addresses after import, address ID is postcode
     *
     * @var array
     */
    protected $_afterImport = array(
        'betsyparker@example.com'   => array(
            'addresses' => array('19107', '72701', '19108'),
            'data' => array(
                'firstname' => 'NotBetsy',
                'lastname'  => 'NotParker',
            ),
        ),
        'anthonyanealy@magento.com' => array('addresses' => array('72701', '92664')),
        'loribbanks@magento.com'    => array('addresses' => array('98801')),
        'kellynilson@magento.com'   => array('addresses' => array()),
    );

    public function setUp()
    {
        $this->_entityAdapter = new Mage_ImportExport_Model_Import_Entity_CustomerComposite();
    }

    public function tearDown()
    {
        unset($this->_entityAdapter);
    }

    /**
     * Test import data method with add/update behaviour
     *
     * @param string $behavior
     * @param string $sourceFile
     * @param array $dataBefore
     * @param array $dataAfter
     * @param array $errors
     *
     * @dataProvider importDataDataProvider
     * @magentoDataFixture Mage/ImportExport/_files/customers_for_address_import.php
     * @covers Mage_ImportExport_Model_Import_Entity_CustomerComposite::_importData
     */
    public function testImportData($behavior, $sourceFile, array $dataBefore, array $dataAfter, array $errors = array())
    {
        $this->markTestIncomplete('Need to be fixed.');

        // set entity adapter parameters
        $this->_entityAdapter->setParameters(array('behavior' => $behavior));

        // set fixture CSV file
        $result = $this->_entityAdapter
            ->setSource(Mage_ImportExport_Model_Import_Adapter::findAdapterFor($sourceFile))
            ->isDataValid();
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
     * Assertion of current customer and address data
     *
     * @param array $expectedData
     */
    protected function _assertCustomerData(array $expectedData)
    {
        /** @var $collection Mage_Customer_Model_Resource_Customer_Collection */
        $collection = Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection');

        $collection->addAttributeToSelect($this->_customerAttributes);
        $customers = $collection->getItems();

        $this->assertSameSize($expectedData, $customers);

        /** @var $customer Mage_Customer_Model_Customer */
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
            /** @var $address Mage_Customer_Model_Address */
            foreach ($addresses as $address) {
                $this->assertContains($address->getData('postcode'), $expectedData[$email]['addresses']);
            }
        }
    }

    /**
     * Data provider for testImportData
     *
     * @return array
     */
    public function importDataDataProvider()
    {
        return array(
            'add_update_behavior' => array(
                '$behavior'   => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
                '$sourceFile' => __DIR__ . '/_files/customer_composite_update.csv',
                '$dataBefore' => $this->_beforeImport,
                '$dataAfter'  => $this->_afterImport,
                '$errors'     => array(array(6)),     // row #6 has no website
            ),
            'delete_behavior' => array(
                '$behavior'   => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
                '$sourceFile' => __DIR__ . '/_files/customer_composite_delete.csv',
                '$dataBefore' => $this->_beforeImport,
                '$dataAfter'  => array(),
            ),
        );
    }
}
