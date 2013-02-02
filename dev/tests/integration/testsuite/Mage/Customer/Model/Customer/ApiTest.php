<?php
/**
 * Test for customer API.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Customer_Model_Customer_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test create method.
     *
     * @magentoDbIsolation enabled
     */
    public function testCreate()
    {
        $customerEmail = uniqid() . '@example.org';
        $customerData = (object)array(
            'email' => $customerEmail,
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'password' => 'password',
            'website_id' => 0,
            'group_id' => 1
        );
        /** Create new customer. */
        $customerId = Magento_Test_Helper_Api::call(
            $this,
            'customerCustomerCreate',
            array('customerData' => $customerData)
        );
        /** Load created customer. */
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('Mage_Customer_Model_Customer');
        $customer->load($customerId);
        /** Assert main customers fields are set. */
        $this->assertEquals($customerEmail, $customer->getEmail(), 'Customer email is not set.');
        $this->assertEquals('Firstname', $customer->getFirstname(), 'Customer first name is not set.');
        $this->assertEquals('Lastname', $customer->getLastname(), 'Customer last name is not set.');
    }

    /**
     * Test info method.
     *
     * @magentoDataFixture Mage/Customer/_files/customer.php
     */
    public function testInfo()
    {
        $customerId = 1;
        /** Retrieve customer data. */
        $customerData = Magento_Test_Helper_Api::call(
            $this,
            'customerCustomerInfo',
            array($customerId)
        );
        /** Assert customer email is set. */
        $this->assertEquals('customer@example.com', $customerData['email'], 'Customer email is invalid.');
        /** Assert response contains base fields. */
        $expectedFields = array('customer_id', 'email', 'firstname', 'lastname', 'password_hash');
        $missingFields = array_diff_key($expectedFields, array_keys($customerData));
        $this->assertEmpty(
            $missingFields,
            sprintf("The following fields must be present in response: %s.", implode(', ', $missingFields))
        );
    }

    /**
     * Test list method.
     *
     * @magentoDataFixture Mage/Customer/_files/two_customers.php
     */
    public function testList()
    {
        /** Retrieve the list of customers. */
        $customersList = Magento_Test_Helper_Api::call(
            $this,
            'customerCustomerList',
            array()
        );
        /** Assert returned customers quantity. */
        $this->assertCount(2, $customersList, 'Returned customers quantity are wrong.');
        /** Assert response contains base fields. */
        $expectedFields = array('customer_id', 'email', 'firstname', 'lastname', 'password_hash');
        $customerData = reset($customersList);
        $missingFields = array_diff_key($expectedFields, array_keys($customerData));
        $this->assertEmpty(
            $missingFields,
            sprintf("The following fields must be present in response: %s.", implode(', ', $missingFields))
        );
    }

    /**
     * Test update method.
     *
     * @magentoDataFixture Mage/Customer/_files/customer.php
     */
    public function testUpdate()
    {
        $customerId = 1;
        $updateCustomerData = (object)array(
            'firstname' => 'new_firstname',
            'email' => 'new_email@example.org'
        );
        /** Update customer. */
        $updateResult = Magento_Test_Helper_Api::call(
            $this,
            'customerCustomerUpdate',
            array('customerId' => $customerId, 'customerData' => $updateCustomerData)
        );
        /** Assert API update operation result. */
        $this->assertTrue($updateResult, 'Customer update is failed.');
        /** Assert fields are updated. */
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('Mage_Customer_Model_Customer')->load($customerId);
        $this->assertEquals(
            'new_firstname',
            $customer->getFirstname(),
            'First name is not updated.'
        );
        $this->assertEquals(
            'new_email@example.org',
            $customer->getEmail(),
            'Email is not updated.'
        );
    }

    /**
     * Test delete method.
     *
     * @magentoDataFixture Mage/Customer/_files/customer.php
     */
    public function testDelete()
    {
        $customerId = 1;
        /** Delete customer. */
        $deleteResult = Magento_Test_Helper_Api::call(
            $this,
            'customerCustomerDelete',
            array('customerId' => $customerId)
        );
        /** Assert delete operation result. */
        $this->assertTrue($deleteResult, 'Customer is not deleted.');
        /** Assert customer is deleted. */
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('Mage_Customer_Model_Customer')->load($customerId);
        $this->assertNull($customer->getEntityId());
    }
}
