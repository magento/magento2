<?php
/**
 * Test class for Mage_Customer_Model_Address_Api.
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @magentoDataFixture Mage/Customer/_files/customer.php
 * @magentoDataFixture Mage/Customer/_files/customer_two_addresses.php
 */
class Mage_Customer_Model_Address_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for customer address list
     */
    public function testCustomerAddressList()
    {
        // Get the customer's addresses
        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'customerAddressList',
            array(
                'customerId' => 1
            )
        );

        $this->assertNotEmpty($soapResult, 'Error during customer address list via API call');
        $this->assertCount(2, $soapResult, 'Result did not contain 2 addresses');

        /** @var $firstAddress Mage_Customer_Model_Address */
        $firstAddress = Mage::getModel('Mage_Customer_Model_Address');
        $firstAddress->load(1);
        $this->_verifyAddress($firstAddress->getData(), $soapResult[0]);

        /** @var $secondAddress Mage_Customer_Model_Address */
        $secondAddress = Mage::getModel('Mage_Customer_Model_Address');
        $secondAddress->load(2);
        $this->_verifyAddress($secondAddress->getData(), $soapResult[1]);
    }

    /**
     * Test for customer address info
     */
    public function testCustomerAddressInfo()
    {
        /** @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = Mage::getModel('Mage_Customer_Model_Address');
        $customerAddress->load(1);

        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'customerAddressInfo',
            array(
                'addressId' => $customerAddress->getId()
            )
        );

        $this->assertNotEmpty($soapResult, 'Error during customer address info via API call');
        $this->_verifyAddress($customerAddress->getData(), $soapResult);
    }

    /**
     * Test customer address create
     *
     * @magentoDbIsolation enabled
     */
    public function testCustomerAddressCreate()
    {
        $customerId = 1;

        // New address to create
        $newAddressData = array(
            'city' => 'Kyle',
            'company' => 'HBM',
            'country_id' => 'US',
            'fax' => '5125551234',
            'firstname' => 'Sherry',
            'lastname' => 'Berry',
            'middlename' => 'Kari',
            'postcode' => '77777',
            'prefix' => 'Ms',
            'region_id' => 35,
            'region' => 'Mississippi',
            'street' => array('123 FM 101'),
            'suffix' => 'M',
            'telephone' => '5',
            'is_default_billing' => false,
            'is_default_shipping' => false
        );

        // Call api to create the address
        $newAddressId = Magento_Test_Helper_Api::call(
            $this,
            'customerAddressCreate',
            array(
                'customerId' => $customerId,
                'addressData' => (object)$newAddressData
            )
        );

        // Verify the new address was added
        /** @var $newAddressModel Mage_Customer_Model_Address */
        $newAddressModel = Mage::getModel('Mage_Customer_Model_Address');
        $newAddressModel->load($newAddressId);

        // Verify all field values were correctly set
        $newAddressData['street'] = trim(implode("\n", $newAddressData['street']));
        $newAddressData['customer_address_id'] = $newAddressId;
        $this->_verifyAddress($newAddressData, $newAddressModel->getData());
    }

    /**
     * Test customer address delete
     *
     * @magentoDbIsolation enabled
     */
    public function testCustomerAddressDelete()
    {
        $addressId = 1;

        // Delete address
        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'customerAddressDelete',
            array(
                'addressId' => $addressId
            )
        );
        $this->assertTrue($soapResult, 'Error during customer address delete via API call');

        /** @var Mage_Customer_Model_Address $address */
        $address = Mage::getModel('Mage_Customer_Model_Address')->load($addressId);
        $this->assertNull($address->getEntityId());
    }

    /**
     * Test customer address update
     *
     * @magentoDbIsolation enabled
     */
    public function testCustomerAddressUpdate()
    {
        $addressId = 1;
        $newFirstname = 'Eric';
        $newTelephone = '888-555-8888';

        // Data to set in existing address
        $updateData = (object)array(
            'firstname' => $newFirstname,
            'telephone' => $newTelephone
        );

        // update a customer's address
        $soapResult = Magento_Test_Helper_Api::call(
            $this,
            'customerAddressUpdate',
            array(
                'addressId' => $addressId,
                'addressData' => $updateData
            )
        );

        $this->assertTrue($soapResult, 'Error during customer address update via API call');

        // Verify all field values were correctly set
        /** @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = Mage::getModel('Mage_Customer_Model_Address');
        $customerAddress->load($addressId);

        $this->assertEquals(
            $newFirstname,
            $customerAddress->getFirstname(),
            'First name is not updated.'
        );
        $this->assertEquals(
            $newTelephone,
            $customerAddress->getTelephone(),
            'Telephone is not updated.'
        );
    }

    /**
     * Verify fields in an address array
     *
     * Compares two arrays containing address data.  Throws assertion error if
     * data does not match.
     *
     * @param array $expectedData Expected values of address array
     * @param array $actualData Values that are to be tested
     */
    protected function _verifyAddress($expectedData, $actualData)
    {
        $fieldsToCompare = array(
            'entity_id' => 'customer_address_id',
            'city',
            'country_id',
            'fax',
            'firstname',
            'lastname',
            'middlename',
            'postcode',
            'region',
            'region_id',
            'street',
            'telephone'
        );

        Magento_Test_Helper_Api::checkEntityFields(
            $this,
            $expectedData,
            $actualData,
            $fieldsToCompare
        );
    }
}
