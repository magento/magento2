<?php
/**
 * Test for \Magento\Customer\Model\AddressRegistry
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

class AddressRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\AddressRegistry
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\AddressRegistry::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testRetrieve()
    {
        $addressId = 1;
        $address = $this->_model->retrieve($addressId);
        $this->assertInstanceOf(\Magento\Customer\Model\Address::class, $address);
        $this->assertEquals($addressId, $address->getId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testRetrieveCached()
    {
        $addressId = 1;
        $addressBeforeDeletion = $this->_model->retrieve($addressId);
        $address2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\Address::class);
        $address2->load($addressId)
            ->delete();
        $addressAfterDeletion = $this->_model->retrieve($addressId);
        $this->assertEquals($addressBeforeDeletion, $addressAfterDeletion);
        $this->assertInstanceOf(\Magento\Customer\Model\Address::class, $addressAfterDeletion);
        $this->assertEquals($addressId, $addressAfterDeletion->getId());
    }

    /**
     */
    public function testRetrieveException()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $addressId = 1;
        $this->_model->retrieve($addressId);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testRemove()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $addressId = 1;
        $address = $this->_model->retrieve($addressId);
        $this->assertInstanceOf(\Magento\Customer\Model\Address::class, $address);
        $address->delete();
        $this->_model->remove($addressId);
        $this->_model->retrieve($addressId);
    }
}
