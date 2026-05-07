<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Block\Address;

use Magento\TestFramework\Helper\Bootstrap;

class BookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Block\Address\Book
     */
    protected $_block;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    protected function setUp(): void
    {
        $blockMock = $this->getMockBuilder(
            \Magento\Framework\View\Element\BlockInterface::class
        )->disableOriginalConstructor()->addMethods(
            ['setTitle']
        )->onlyMethods(
            ['toHtml']
        )->getMock();

        $blockMock->expects($this->any())->method('setTitle');

        $this->currentCustomer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Customer\Helper\Session\CurrentCustomer::class);
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(\Magento\Framework\View\LayoutInterface::class);
        $layout->setBlock('head', $blockMock);
        $this->_block = $layout
            ->createBlock(
                \Magento\Customer\Block\Address\Book::class,
                '',
                ['currentCustomer' => $this->currentCustomer]
            );
    }

    protected function tearDown(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $objectManager->get(\Magento\Customer\Model\CustomerRegistry::class);
        // Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    public function testGetAddressEditUrl()
    {
        $this->assertEquals(
            'http://localhost/index.php/customer/address/edit/id/1/',
            $this->_block->getAddressEditUrl(1)
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @dataProvider hasPrimaryAddressDataProvider
     * @magentoAppIsolation enabled
     */
    public function testHasPrimaryAddress($customerId, $expected)
    {
        if (!empty($customerId)) {
            $this->currentCustomer->setCustomerId($customerId);
        }
        $this->assertEquals($expected, $this->_block->hasPrimaryAddress());
    }

    public static function hasPrimaryAddressDataProvider()
    {
        return ['0' => [0, false], '1' => [1, true], '5' => [5, false]];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testGetAdditionalAddresses()
    {
        $this->currentCustomer->setCustomerId(1);
        $this->assertNotNull($this->_block->getAdditionalAddresses());
        $this->assertCount(1, $this->_block->getAdditionalAddresses());
        $this->assertInstanceOf(
            \Magento\Customer\Api\Data\AddressInterface::class,
            $this->_block->getAdditionalAddresses()[0]
        );
        $this->assertEquals(2, $this->_block->getAdditionalAddresses()[0]->getId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @dataProvider getAdditionalAddressesDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetAdditionalAddressesNegative($customerId, $expected)
    {
        if (!empty($customerId)) {
            $this->currentCustomer->setCustomerId($customerId);
        }
        $this->assertEquals($expected, $this->_block->getAdditionalAddresses());
    }

    public static function getAdditionalAddressesDataProvider()
    {
        return ['0' => [0, false], '5' => [5, false]];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddressHtml()
    {
        $expected = "John Smith<br />\nCompanyName<br />\nGreen str, 67<br />\n\n\n\nCityM,  Alabama, 75477<br />" .
            "\nUnited States<br />\nT: <a href=\"tel:3468676\">3468676</a>\n\n";
        $address = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AddressRepositoryInterface::class
        )->getById(1);
        $html = $this->_block->getAddressHtml($address);
        $this->assertEquals($expected, $html);
    }

    public function testGetAddressHtmlWithoutAddress()
    {
        $this->assertEquals('', $this->_block->getAddressHtml(null));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetCustomer()
    {
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $customer = $customerRepository->getById(1);

        $this->currentCustomer->setCustomerId(1);
        $object = $this->_block->getCustomer();
        $this->assertEquals($customer, $object);
    }

    public function testGetCustomerMissingCustomer()
    {
        $this->assertNull($this->_block->getCustomer());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @dataProvider getDefaultBillingDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetDefaultBilling($customerId, $expected)
    {
        $this->currentCustomer->setCustomerId($customerId);
        $this->assertEquals($expected, $this->_block->getDefaultBilling());
    }

    public static function getDefaultBillingDataProvider()
    {
        return ['0' => [0, null], '1' => [1, 1], '5' => [5, null]];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @dataProvider getDefaultShippingDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetDefaultShipping($customerId, $expected)
    {
        if (!empty($customerId)) {
            $this->currentCustomer->setCustomerId($customerId);
        }
        $this->assertEquals($expected, $this->_block->getDefaultShipping());
    }

    public static function getDefaultShippingDataProvider()
    {
        return ['0' => [0, null], '1' => [1, 1], '5' => [5, null]];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testGetAddressById()
    {
        $this->assertNull($this->_block->getAddressById(1), 'Should return null when no customer is logged in');

        $this->assertNull(
            $this->_block->getAddressById(999),
            'Should return null for non-existent address when no customer'
        );
    }

    /**
     * Test getAddressById with mocked customer and created test data
     * @return void
     */
    public function testGetAddressByIdWithMockedCustomer(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $uniqueEmail = 'test_' . uniqid() . '@example.com';
        $customer = $objectManager->create(\Magento\Customer\Model\Customer::class);
        $customer->setWebsiteId(1)
            ->setEmail($uniqueEmail)
            ->setPassword('password')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setFirstname('Test')
            ->setLastname('Customer');
        $customer->isObjectNew(true);
        $customer->save();
        $customerId = $customer->getId();

        $address = $objectManager->create(\Magento\Customer\Model\Address::class);
        $address->isObjectNew(true);
        $address->setData([
            'attribute_set_id' => 2,
            'telephone' => '1234567890',
            'postcode' => '12345',
            'country_id' => 'US',
            'city' => 'Test City',
            'street' => ['123 Test Street'],
            'lastname' => 'Customer',
            'firstname' => 'Test',
            'parent_id' => $customerId,
            'region_id' => 1,
        ])->setCustomerId($customerId);
        $address->save();
        $addressId = $address->getId();

        $mockCustomer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $mockCustomer->method('getId')->willReturn($customerId);

        $mockCurrentCustomer = $this->getMockBuilder(\Magento\Customer\Helper\Session\CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCurrentCustomer->method('getCustomer')->willReturn($mockCustomer);

        $layout = $objectManager->get(\Magento\Framework\View\LayoutInterface::class);
        $block = $layout->createBlock(
            \Magento\Customer\Block\Address\Book::class,
            '',
            ['currentCustomer' => $mockCurrentCustomer]
        );

        $retrievedAddress = $block->getAddressById($addressId);
        $this->assertInstanceOf(\Magento\Customer\Api\Data\AddressInterface::class, $retrievedAddress);
        $this->assertEquals($addressId, $retrievedAddress->getId());
        $this->assertEquals($customerId, $retrievedAddress->getCustomerId());

        $this->assertNull($block->getAddressById(999), 'Should return null for non-existent address');

        $otherUniqueEmail = 'other_' . uniqid() . '@example.com';
        $otherCustomer = $objectManager->create(\Magento\Customer\Model\Customer::class);
        $otherCustomer->setWebsiteId(1)
            ->setEmail($otherUniqueEmail)
            ->setPassword('password')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setFirstname('Other')
            ->setLastname('Customer');
        $otherCustomer->isObjectNew(true);
        $otherCustomer->save();
        $otherCustomerId = $otherCustomer->getId();

        $otherAddress = $objectManager->create(\Magento\Customer\Model\Address::class);
        $otherAddress->isObjectNew(true);
        $otherAddress->setData([
            'attribute_set_id' => 2,
            'telephone' => '0987654321',
            'postcode' => '54321',
            'country_id' => 'US',
            'city' => 'Other City',
            'street' => ['456 Other Street'],
            'lastname' => 'Customer',
            'firstname' => 'Other',
            'parent_id' => $otherCustomerId,
            'region_id' => 1,
        ])->setCustomerId($otherCustomerId);
        $otherAddress->save();
        $otherAddressId = $otherAddress->getId();

        $this->assertNull(
            $block->getAddressById($otherAddressId),
            'Should return null for address belonging to different customer'
        );
    }
}
