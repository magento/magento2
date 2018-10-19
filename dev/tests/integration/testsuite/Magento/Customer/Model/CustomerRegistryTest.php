<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Customer\Model\CustomerRegistry
 */
class CustomerRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $_model;

    /**#@+
     * Data set in customer fixture
     */
    const CUSTOMER_ID = 1;
    const CUSTOMER_EMAIL = 'customer@example.com';
    const WEBSITE_ID = 1;

    /**
     * Initialize SUT
     */
    protected function setUp()
    {
        $this->_model = Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\CustomerRegistry::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testRetrieve()
    {
        $customer = $this->_model->retrieve(self::CUSTOMER_ID);
        $this->assertInstanceOf(\Magento\Customer\Model\Customer::class, $customer);
        $this->assertEquals(self::CUSTOMER_ID, $customer->getId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testRetrieveByEmail()
    {
        $customer = $this->_model->retrieveByEmail('customer@example.com', self::WEBSITE_ID);
        $this->assertInstanceOf(\Magento\Customer\Model\Customer::class, $customer);
        $this->assertEquals(self::CUSTOMER_EMAIL, $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppArea adminhtml
     */
    public function testRetrieveCached()
    {
        //Setup customer in the id and email registries
        $customerBeforeDeletion = $this->_model->retrieve(self::CUSTOMER_ID);
        //Delete the customer from db
        Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Customer::class
        )->load(self::CUSTOMER_ID)->delete();
        //Verify presence of Customer in registry
        $this->assertEquals($customerBeforeDeletion, $this->_model->retrieve(self::CUSTOMER_ID));
        //Verify presence of Customer in email registry
        $this->assertEquals($customerBeforeDeletion, $this->_model
                ->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId = 1
     */
    public function testRetrieveException()
    {
        $this->_model->retrieve(self::CUSTOMER_ID);
    }

    public function testRetrieveEmailException()
    {
        try {
            $this->_model->retrieveByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
            $this->fail("NoSuchEntityException was not thrown as expected.");
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'email',
                'fieldValue' => 'customer@example.com',
                'field2Name' => 'websiteId',
                'field2Value' => 1,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @magentoAppArea adminhtml
     */
    public function testRemove()
    {
        $customer = $this->_model->retrieve(self::CUSTOMER_ID);
        $this->assertInstanceOf(\Magento\Customer\Model\Customer::class, $customer);
        $customer->delete();
        $this->_model->remove(self::CUSTOMER_ID);
        $this->_model->retrieve(self::CUSTOMER_ID);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @magentoAppArea adminhtml
     */
    public function testRemoveByEmail()
    {
        $customer = $this->_model->retrieve(self::CUSTOMER_ID);
        $this->assertInstanceOf(\Magento\Customer\Model\Customer::class, $customer);
        $customer->delete();
        $this->_model->removeByEmail(self::CUSTOMER_EMAIL, self::WEBSITE_ID);
        $this->_model->retrieveByEmail(self::CUSTOMER_EMAIL, $customer->getWebsiteId());
    }
}
