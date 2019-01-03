<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Address;

use Magento\TestFramework\Helper\Bootstrap;

class BookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $blockMock */
        $blockMock = $this->getMockBuilder(
            \Magento\Framework\View\Element\BlockInterface::class
        )->disableOriginalConstructor()->setMethods(
            ['setTitle', 'toHtml']
        )->getMock();
        $blockMock->expects($this->any())->method('setTitle');

        $this->currentCustomer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Customer\Helper\Session\CurrentCustomer::class);
        $this->layout = Bootstrap::getObjectManager()->get(\Magento\Framework\View\LayoutInterface::class);
        $this->layout->setBlock('head', $blockMock);
    }

    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $objectManager->get(\Magento\Customer\Model\CustomerRegistry::class);
        // Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddressEditUrl()
    {
        $bookBlock = $this->createBlockForCustomer(1);

        $this->assertEquals(
            'http://localhost/index.php/customer/address/edit/id/1/',
            $bookBlock->getAddressEditUrl(1)
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @dataProvider hasPrimaryAddressDataProvider
     * @param int $customerId
     * @param bool $expected
     * @magentoAppIsolation enabled
     */
    public function testHasPrimaryAddress($customerId, $expected)
    {
        $bookBlock = $this->createBlockForCustomer($customerId);
        $this->assertEquals($expected, $bookBlock->hasPrimaryAddress());
    }

    public function hasPrimaryAddressDataProvider()
    {
        return ['1' => [1, true], '5' => [5, false]];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testGetAdditionalAddresses()
    {
        $bookBlock = $this->createBlockForCustomer(1);
        $this->assertNotNull($bookBlock->getAdditionalAddresses());
        $this->assertCount(1, $bookBlock->getAdditionalAddresses());
        $this->assertInstanceOf(
            \Magento\Customer\Api\Data\AddressInterface::class,
            $bookBlock->getAdditionalAddresses()[0]
        );
        $this->assertEquals(2, $bookBlock->getAdditionalAddresses()[0]->getId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @dataProvider getAdditionalAddressesDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetAdditionalAddressesNegative($customerId, $expected)
    {
        $bookBlock = $this->createBlockForCustomer($customerId);
        $this->currentCustomer->setCustomerId($customerId);
        $this->assertEquals($expected, $bookBlock->getAdditionalAddresses());
    }

    public function getAdditionalAddressesDataProvider()
    {
        return ['5' => [5, false]];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddressHtml()
    {
        $bookBlock = $this->createBlockForCustomer(1);
        $expected = "John Smith<br />\nCompanyName<br />\nGreen str, 67<br />\n\n\n\nCityM,  Alabama, 75477<br />" .
            "\nUnited States<br />\nT: <a href=\"tel:3468676\">3468676</a>\n\n";
        $address = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AddressRepositoryInterface::class
        )->getById(1);
        $html = $bookBlock->getAddressHtml($address);
        $this->assertEquals($expected, $html);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddressHtmlWithoutAddress()
    {
        $bookBlock = $this->createBlockForCustomer(5);
        $this->assertEquals('', $bookBlock->getAddressHtml(null));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetCustomer()
    {
        $bookBlock = $this->createBlockForCustomer(1);
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $customer = $customerRepository->getById(1);
        $object = $bookBlock->getCustomer();
        $this->assertEquals($customer, $object);
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
        $bookBlock = $this->createBlockForCustomer($customerId);
        $this->assertEquals($expected, $bookBlock->getDefaultBilling());
    }

    public function getDefaultBillingDataProvider()
    {
        return ['1' => [1, 1], '5' => [5, null]];
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
        $bookBlock = $this->createBlockForCustomer($customerId);
        $this->currentCustomer->setCustomerId($customerId);
        $this->assertEquals($expected, $bookBlock->getDefaultShipping());
    }

    public function getDefaultShippingDataProvider()
    {
        return ['1' => [1, 1], '5' => [5, null]];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddressById()
    {
        $bookBlock = $this->createBlockForCustomer(1);
        $this->assertInstanceOf(\Magento\Customer\Api\Data\AddressInterface::class, $bookBlock->getAddressById(1));
    }

    /**
     * Create address book block for customer
     *
     * @param int $customerId
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    private function createBlockForCustomer($customerId)
    {
        $this->currentCustomer->setCustomerId($customerId);
        return $this->layout->createBlock(
            \Magento\Customer\Block\Address\Book::class,
            '',
            ['currentCustomer' => $this->currentCustomer]
        );
    }
}
