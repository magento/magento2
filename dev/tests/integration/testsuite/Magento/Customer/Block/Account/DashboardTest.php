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
namespace Magento\Customer\Block\Account;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;

class DashboardTest extends \PHPUnit_Framework_TestCase
{
    /** @var Dashboard */
    private $block;

    /** @var \Magento\Customer\Model\Session */
    private $customerSession;

    /** @var CustomerAccountServiceInterface */
    private $customerAccountService;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $this->customerAccountService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );

        $this->block = Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Account\Dashboard',
            '',
            array(
                'customerSession' => $this->customerSession,
                'customerAccountService' => $this->customerAccountService
            )
        );
    }

    /**
     * Execute per test cleanup.
     */
    public function tearDown()
    {
        $this->customerSession->unsCustomerId();

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
        $customer = $this->customerAccountService->getCustomer(1);
        $this->customerSession->setCustomerId(1);
        $object = $this->block->getCustomer();
        $this->assertEquals($customer, $object);
        $this->assertInstanceOf('Magento\Customer\Service\V1\Data\Customer', $object);
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
        $customer = $this->customerAccountService->getCustomer(1);
        $this->customerSession->setCustomerId(1);
        $addresses = $this->block->getPrimaryAddresses();
        $this->assertCount(1, $addresses);
        $address = $addresses[0];
        $this->assertInstanceOf('Magento\Customer\Service\V1\Data\Address', $address);
        $this->assertEquals($customer->getDefaultBilling(), $address->getId());
        $this->assertEquals($customer->getDefaultShipping(), $address->getId());
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
