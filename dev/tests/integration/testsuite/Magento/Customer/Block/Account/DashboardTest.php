<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\TestFramework\Helper\Bootstrap;

class DashboardTest extends \PHPUnit_Framework_TestCase
{
    /** @var Dashboard */
    private $block;

    /** @var \Magento\Customer\Model\Session */
    private $customerSession;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $this->customerRepository = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );

        $this->block = Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Account\Dashboard',
            '',
            [
                'customerSession' => $this->customerSession,
                'customerRepository' => $this->customerRepository
            ]
        );
    }

    /**
     * Execute per test cleanup.
     */
    public function tearDown()
    {
        $this->customerSession->setCustomerId(null);

        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Customer\Model\CustomerRegistry');
        //Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * Verify that the Dashboard::getCustomer() method returns a valid Customer Data.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomer()
    {
        $customer = $this->customerRepository->getById(1);
        $this->customerSession->setCustomerId(1);
        $object = $this->block->getCustomer();
        $this->assertEquals($customer, $object);
        $this->assertInstanceOf('Magento\Customer\Api\Data\CustomerInterface', $object);
    }

    /**
     * Verify that the specified customer has neither a default billing no shipping address.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     */
    public function testGetPrimaryAddressesNoAddresses()
    {
        $this->customerSession->setCustomerId(5);
        $this->assertFalse($this->block->getPrimaryAddresses());
    }

    /**
     * Verify that the specified customer has the same default billing and shipping address.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testGetPrimaryAddressesBillingShippingSame()
    {
        $customer = $this->customerRepository->getById(1);
        $this->customerSession->setCustomerId(1);
        $addresses = $this->block->getPrimaryAddresses();
        $this->assertCount(1, $addresses);
        $address = $addresses[0];
        $this->assertInstanceOf('Magento\Customer\Api\Data\AddressInterface', $address);
        $this->assertEquals((int)$customer->getDefaultBilling(), $address->getId());
        $this->assertEquals((int)$customer->getDefaultShipping(), $address->getId());
    }

    /**
     * Verify that the specified customer has different default billing and shipping addresses.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_primary_addresses.php
     */
    public function testGetPrimaryAddressesBillingShippingDifferent()
    {
        $this->customerSession->setCustomerId(1);
        $addresses = $this->block->getPrimaryAddresses();
        $this->assertCount(2, $addresses);
        $this->assertNotEquals($addresses[0], $addresses[1]);
        $this->assertTrue($addresses[0]->isDefaultBilling());
        $this->assertTrue($addresses[1]->isDefaultShipping());
    }
}
