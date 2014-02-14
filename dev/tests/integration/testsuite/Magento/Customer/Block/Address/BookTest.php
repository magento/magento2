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

namespace Magento\Customer\Block\Address;

class BookTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Block\Address\Book
     */
    protected $_block;

    /** @var  \Magento\Customer\Model\Session */
    protected $_customerSession;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $blockMock */
        $blockMock = $this->getMockBuilder('\Magento\View\Element\BlockInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('setTitle', 'toHtml'))
            ->getMock();
        $blockMock->expects($this->any())
            ->method('setTitle');

        $this->_customerSession = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('\Magento\Customer\Model\Session');
        /** @var \Magento\View\LayoutInterface $layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\LayoutInterface');
        $layout->setBlock('head', $blockMock);
        $this->_block = $layout
            ->createBlock(
                'Magento\Customer\Block\Address\Book',
                '',
                ['customerSession' => $this->_customerSession]
            );
    }

    protected function tearDown()
    {
        $this->_customerSession->unsCustomerId();
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
     */
    public function testHasPrimaryAddress($customerId, $expected)
    {
        if (!empty($customerId)) {
            $this->_customerSession->setCustomerId($customerId);
        }
        $this->assertEquals($expected, $this->_block->hasPrimaryAddress());
    }

    public function hasPrimaryAddressDataProvider()
    {
        return [
            '0' => [0, false],
            '1' => [1, true],
            '5' => [5, false],
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testGetAdditionalAddresses()
    {
        $this->_customerSession->setCustomerId(1);
        $this->assertNotNull($this->_block->getAdditionalAddresses());
        $this->assertCount(1, $this->_block->getAdditionalAddresses());
        $this->assertInstanceOf(
            '\Magento\Customer\Service\V1\Dto\Address',
            $this->_block->getAdditionalAddresses()[0]
        );
        $this->assertEquals(2, $this->_block->getAdditionalAddresses()[0]->getId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @dataProvider getAdditionalAddressesDataProvider
     */
    public function testGetAdditionalAddressesNegative($customerId, $expected)
    {
        if (!empty($customerId)) {
            $this->_customerSession->setCustomerId($customerId);
        }
        $this->assertEquals($expected, $this->_block->getAdditionalAddresses());
    }

    public function getAdditionalAddressesDataProvider()
    {
        return [
            '0' => [0, false],
            '5' => [5, false],
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testGetAddressHtml()
    {
        $expected = "John Smith<br/>\n\nGreen str, 67<br />\n\n\n\nCityM,  Alabama, 75477<br/>\n<br/>\nT: 3468676\n\n";
        $address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerAddressServiceInterface')
            ->getAddressById(1);
        $html = $this->_block->getAddressHtml($address);
        $this->assertEquals($expected, $html);
    }

    public function testGetAddressHtmlWithoutAddress()
    {
        $this->assertEquals('', $this->_block->getAddressHtml(null));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomer()
    {
        $customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerServiceInterface')->getCustomer(1);

        $this->_customerSession->setCustomerId(1);
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
     */
    public function testGetDefaultBilling($customerId, $expected)
    {
        if (!empty($customerId)) {
            $this->_customerSession->setCustomerId($customerId);
        }
        $this->assertEquals($expected, $this->_block->getDefaultBilling());
    }

    public function getDefaultBillingDataProvider()
    {
        return [
            '0' => [0, Null],
            '1' => [1, 1],
            '5' => [5, Null],
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     * @dataProvider getDefaultShippingDataProvider
     */
    public function testGetDefaultShipping($customerId, $expected)
    {
        if (!empty($customerId)) {
            $this->_customerSession->setCustomerId($customerId);
        }
        $this->assertEquals($expected, $this->_block->getDefaultShipping());
    }

    public function getDefaultShippingDataProvider()
    {
        return [
            '0' => [0, Null],
            '1' => [1, 1],
            '5' => [5, Null],
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testGetAddressById()
    {
        $this->assertInstanceOf(
            '\Magento\Customer\Service\V1\Dto\Address',
            $this->_block->getAddressById(1)
        );
        $this->assertNull($this->_block->getAddressById(5));
    }
}
