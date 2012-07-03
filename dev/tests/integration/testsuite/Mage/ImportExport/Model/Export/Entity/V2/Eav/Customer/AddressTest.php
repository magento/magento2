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
 * Test for customer address export model V2
 *
 * @group module:Mage_ImportExport
 * magentoDataFixture Mage/ImportExport/_files/customer_with_addresses.php
 */
class Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer_AddressTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer_Address
     */
    protected $_model;

    /**
     * List of existing websites
     *
     * @var array
     */
    protected $_websites = array();

    protected function setUp()
    {
        parent::setUp();
        $this->_model = new Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer_Address();

        /** @var $website Mage_Core_Model_Website */
        foreach (Mage::app()->getWebsites(true) as $website) {
            $this->_websites[$website->getId()] = $website->getCode();
        }
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_websites);

        parent::tearDown();
    }

    /**
     * Test export method
     */
    public function testExport()
    {
        $this->markTestIncomplete('BUG MAGETWO-1953');

        $websiteCode = Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer_Address::COL_WEBSITE;
        $emailCode = Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer_Address::COL_EMAIL;
        $entityIdCode = Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer_Address::COL_ADDRESS_ID;

        $expectedAttributes = array();
        /** @var $collection Mage_Customer_Model_Resource_Address_Attribute_Collection */
        $collection = Mage::getResourceModel('Mage_Customer_Model_Resource_Address_Attribute_Collection');
        /** @var $attribute Mage_Customer_Model_Attribute */
        foreach ($collection as $attribute) {
            $expectedAttributes[] = $attribute->getAttributeCode();
        }

        // Get customer default addresses column name to customer attribute mapping array.
        $defaultAddressMap = Mage_ImportExport_Model_Import_Entity_Customer_Address::getDefaultAddressAttrMapping();

        $this->_model->setWriter(new Mage_ImportExport_Model_Export_Adapter_Csv());

        $data = $this->_csvToArray($this->_model->export(), $entityIdCode);

        $this->assertEquals(
            count($expectedAttributes),
            count(array_intersect($expectedAttributes, $data['header'])),
            'Expected attribute codes were not exported'
        );

        $this->assertNotEmpty($data['data'], 'No data was exported');

        // Get addresses
        /** @var $customers Mage_Customer_Model_Customer[] */
        $customers = Mage::registry('_fixture/Mage_ImportExport_Customers_Array');
        foreach ($customers as $customer) {
            /** @var $address Mage_Customer_Model_Address */
            foreach ($customer->getAddresses() as $address) {
                // Check unique key
                $data['data'][$address->getId()][$websiteCode] = $this->_websites[$customer->getWebsiteId()];
                $data['data'][$address->getId()][$emailCode] = $customer->getEmail();
                $data['data'][$address->getId()][$entityIdCode] = $address->getId();

                // Check by expected attributes
                foreach ($expectedAttributes as $code) {
                    if (!in_array($code, $this->_model->getDisabledAttributes())) {
                        $this->assertEquals(
                            $address->getData($code),
                            $data['data'][$address->getId()][$code],
                            'Attribute "' . $code . '" is not equal'
                        );
                    }
                }

                // Check customer default addresses column name to customer attribute mapping array
                foreach ($defaultAddressMap as $exportCode => $code) {
                    $this->assertEquals(
                        $address->getData($code),
                        (int) $data['data'][$address->getId()][$exportCode],
                        'Attribute "' . $code . '" is not equal'
                    );
                }
            }
        }
    }

    /**
     * Get possible gender values for filter
     *
     * @return array
     */
    public function getGenderFilterValueDataProvider()
    {
        return array(
            'male'   => array('$genderFilterValue' => 1),
            'female' => array('$genderFilterValue' => 2)
        );
    }

    /**
     * Test export method if filter was set
     *
     * @dataProvider getGenderFilterValueDataProvider
     *
     * @param int $genderFilterValue
     */
    public function testExportWithFilter($genderFilterValue)
    {
        $this->markTestIncomplete('BUG MAGETWO-1953');

        $entityIdCode = Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer_Address::COL_ADDRESS_ID;

        $this->_model->setWriter(new Mage_ImportExport_Model_Export_Adapter_Csv());

        $filterData = array(
            'export_filter' => array(
                'gender' => $genderFilterValue
            )
        );

        $this->_model->setParameters($filterData);

        // Get expected address count
        /** @var $customers Mage_Customer_Model_Customer[] */
        $customers = Mage::registry('_fixture/Mage_ImportExport_Customers_Array');
        $expectedCount = 0;
        foreach ($customers as $customer) {
            if ($customer->getGender() == $genderFilterValue) {
                $expectedCount += count($customer->getAddresses());
            }
        }

        $data = $this->_csvToArray($this->_model->export(), $entityIdCode);

        $this->assertCount($expectedCount, $data['data']);
    }

    /**
     * Test entity type code value
     */
    public function testGetEntityTypeCode()
    {
        $this->markTestIncomplete('BUG MAGETWO-1953');

        $this->assertEquals('customer_address', $this->_model->getEntityTypeCode());
    }

    /**
     * Test type of attribute collection
     */
    public function testGetAttributeCollection()
    {
        $this->markTestIncomplete('BUG MAGETWO-1953');

        $this->assertInstanceOf('Mage_Customer_Model_Resource_Address_Attribute_Collection',
            $this->_model->getAttributeCollection()
        );
    }

    /**
     * Export CSV string to array
     *
     * @param string $content
     * @param mixed $entityId
     * @return array
     */
    protected function _csvToArray($content, $entityId = null)
    {
        $data = array(
            'header' => array(),
            'data'   => array()
        );

        $lines = str_getcsv($content, "\n");
        foreach ($lines as $index => $line) {
            if ($index == 0) {
                $data['header'] = str_getcsv($line);
            } else {
                $row = array_combine($data['header'], str_getcsv($line));
                if (!is_null($entityId) && !empty($row[$entityId])) {
                    $data['data'][$row[$entityId]] = $row;
                } else {
                    $data['data'][] = $row;
                }
            }
        }

        return $data;
    }

    /**
     * Test filename getter. Filename must be set in constructor.
     */
    public function testGetFileName()
    {
        $this->markTestIncomplete('BUG MAGETWO-1953');

        $this->assertEquals($this->_model->getEntityTypeCode(), $this->_model->getFileName());
    }
}
