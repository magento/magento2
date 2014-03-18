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

namespace Magento\Customer\Service\V1\Data;

class CustomerDetailsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Customer builder mock
     *
     * @var CustomerBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerBuilderMock;

    /**
     * Address builder mock
     *
     * @var AddressBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_addressBuilderMock;

    /**
     * Customer mock
     *
     * @var Customer | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerMock;

    /**
     * Address mock
     *
     * @var Address | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_addressMock;

    protected function setUp()
    {
        $this->_customerBuilderMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\CustomerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_addressBuilderMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\AddressBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_customerMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_addressMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\Address')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstructor()
    {
        $this->_customerBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('customer'));
        $customerDetailsBuilder = new CustomerDetailsBuilder(
            $this->_customerBuilderMock,
            $this->_addressBuilderMock
        );
        $customerDetails = $customerDetailsBuilder->create();
        $this->assertEquals('customer', $customerDetails->getCustomer());
        $this->assertEquals(null, $customerDetails->getAddresses());
    }

    public function testSetCustomer()
    {
        $this->_customerBuilderMock->expects($this->never())
            ->method('create')
            ->will($this->returnValue('customer'));
        $customerDetailsBuilder = new CustomerDetailsBuilder(
            $this->_customerBuilderMock,
            $this->_addressBuilderMock
        );
        $customerDetails = $customerDetailsBuilder->setCustomer($this->_customerMock)->create();
        $this->assertEquals($this->_customerMock, $customerDetails->getCustomer());
        $this->assertEquals(null, $customerDetails->getAddresses());
    }

    public function testSetAddresses()
    {
        $this->_customerBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('customer'));
        $customerDetailsBuilder = new CustomerDetailsBuilder(
            $this->_customerBuilderMock,
            $this->_addressBuilderMock
        );
        $customerDetails = $customerDetailsBuilder
            ->setAddresses([$this->_addressMock, $this->_addressMock])->create();
        $this->assertEquals('customer', $customerDetails->getCustomer());
        $this->assertEquals([$this->_addressMock, $this->_addressMock], $customerDetails->getAddresses());
    }

    /**
     * @param array $data
     * @param Customer $expectedCustomer
     * @param Address[] $expectedAddresses
     * @dataProvider populateWithArrayDataProvider
     */
    public function testPopulateWithArray($data, $expectedCustomerStr, $expectedAddressesStr)
    {

        $expectedCustomer = ($expectedCustomerStr == 'customerMock') ? $this->_customerMock : $expectedCustomerStr;
        $expectedAddresses = null;
        if (isset($expectedAddressesStr)) {
            $expectedAddresses = [];
            foreach ($expectedAddressesStr as $addressStr ) {
                $expectedAddresses[] = ($addressStr == 'addressMock') ? $this->_addressMock : $addressStr;
            }
        }
        $this->_customerBuilderMock->expects($this->any())
            ->method('populateWithArray')
            ->will($this->returnValue($this->_customerBuilderMock));
        $this->_customerBuilderMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerMock));

        $this->_addressBuilderMock->expects($this->any())
            ->method('populateWithArray')
            ->will($this->returnValue($this->_addressBuilderMock));
        $this->_addressBuilderMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_addressMock));

        $customerDetailsBuilder = new CustomerDetailsBuilder(
            $this->_customerBuilderMock,
            $this->_addressBuilderMock
        );
        $customerDetails = $customerDetailsBuilder->populateWithArray($data)->create();
        $this->assertEquals($expectedCustomer, $customerDetails->getCustomer());
        $this->assertEquals($expectedAddresses, $customerDetails->getAddresses());
    }

    public function populateWithArrayDataProvider()
    {
        return [
            [['customer' => ['customerData']], 'customerMock', null],
            [['customer' => ['customerData'], 'addresses' => []], 'customerMock', []],
            [['customer' => ['customerData'], 'addresses' => null], 'customerMock', null],
            [
                ['customer' => ['customerData'], 'addresses' => [['addressData']]],
                'customerMock',
                ['addressMock'],
            ],
            [
                ['customer' => ['customerData'], 'addresses' => [['addressData'], ['addressData']]],
                'customerMock',
                ['addressMock', 'addressMock'],
            ],
            [['addresses' => [['addressData']]], 'customerMock', ['addressMock'],],
            [['customer' => null, 'addresses' => [['addressData']]], 'customerMock', ['addressMock'],],
            [[], 'customerMock', null,],
        ];
    }
}
