<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Test\Unit\Model\Express\Checkout;

/**
 * Class QuoteTest
 */
class QuoteTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Express\Checkout\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactoryMock;

    /**
     * @var \Magento\Framework\Object\Copy|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $copyObjectMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    public function setUp()
    {
        $this->addressFactoryMock = $this->getMock(
            'Magento\Customer\Api\Data\AddressInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->copyObjectMock = $this->getMock(
            'Magento\Framework\Object\Copy',
            [],
            [],
            '',
            false
        );
        $this->customerFactoryMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\CustomerRepositoryInterface',
            ['getById'],
            '',
            false,
            true,
            true,
            []
        );
        $this->quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            [],
            [],
            '',
            false
        );
        $this->addressMock = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $this->dataObjectHelper = $this->getMock(
            'Magento\Framework\Api\DataObjectHelper',
            ['populateWithArray'],
            [],
            '',
            false
        );

        $this->quote = new \Magento\Paypal\Model\Express\Checkout\Quote(
            $this->addressFactoryMock,
            $this->customerFactoryMock,
            $this->customerRepositoryMock,
            $this->copyObjectMock,
            $this->dataObjectHelper
        );
    }

    public function testPrepareQuoteForNewCustomer()
    {
        $customerAddressMock = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressMock->expects($this->any())
            ->method('exportCustomerAddress')
            ->willReturn($customerAddressMock);
        $this->addressMock->expects($this->any())
            ->method('getData')
            ->willReturn([]);
        $this->addressFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->addressMock);

        $this->quoteMock->expects($this->any())
            ->method('isVirtual')
            ->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($this->addressMock);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);

        $customerDataMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerDataMock);

        $this->copyObjectMock->expects($this->any())
            ->method('getDataFromFieldset')
            ->willReturn([]);

        $this->dataObjectHelper->expects($this->any())
            ->method('populateWithArray')
            ->willReturnSelf();

        $this->assertInstanceOf(
            'Magento\Quote\Model\Quote',
            $this->quote->prepareQuoteForNewCustomer($this->quoteMock)
        );
    }

    /**
     * @param $conditions
     * @param $exportShippingCustomerAddressCalls
     * @param $exportBillingCustomerAddressCalls
     * @dataProvider prepareRegisteredCustomerQuoteDataProvider
     */
    public function testPrepareRegisteredCustomerQuote($conditions, $exportBillingCustomerAddressCalls,
        $exportShippingCustomerAddressCalls)
    {
        $customerDataMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->setMethods(['getDefaultShipping', 'getDefaultBilling'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerDataMock->expects($this->any())
            ->method('getDefaultBilling')
            ->willReturn($conditions['isDefaultBilling']);
        $customerDataMock->expects($this->any())
            ->method('getDefaultShipping')
            ->willReturn($conditions['isDefaultShipping']);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($customerDataMock);

        $customerAddressMock = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $shippingAddressMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Address')
            ->setMethods(['getTelephone', 'getSameAsBilling', 'getCustomerId', 'getSaveInAddressBook', 'exportCustomerAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $shippingAddressMock->expects($this->any())
            ->method('getTelephone')
            ->willReturn($conditions['isTelephone']);
        $shippingAddressMock->expects($this->any())
            ->method('getSameAsBilling')
            ->willReturn($conditions['isShippingSameAsBilling']);
        $shippingAddressMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(true);
        $shippingAddressMock->expects($this->any())
            ->method('getSaveInAddressBook')
            ->willReturn($conditions['isShippingSaveInAddressBook']);
        $shippingAddressMock->expects($this->exactly($exportShippingCustomerAddressCalls))
            ->method('exportCustomerAddress')
            ->willReturn($customerAddressMock);

        $billingAddressMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Address')
            ->setMethods(['getTelephone', 'getCustomerId', 'getSaveInAddressBook', 'exportCustomerAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $billingAddressMock->expects($this->any())
            ->method('getTelephone')
            ->willReturn($conditions['isTelephone']);
        $billingAddressMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(true);
        $billingAddressMock->expects($this->any())
            ->method('getSaveInAddressBook')
            ->willReturn($conditions['isBillingSaveInAddressBook']);
        $billingAddressMock->expects($this->exactly($exportBillingCustomerAddressCalls))
            ->method('exportCustomerAddress')
            ->willReturn($customerAddressMock);

        $this->quoteMock->expects($this->any())
            ->method('isVirtual')
            ->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddressMock);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($shippingAddressMock);

        $this->assertInstanceOf(
            'Magento\Quote\Model\Quote',
            $this->quote->prepareRegisteredCustomerQuote($this->quoteMock, 1)
        );
    }

    /**
     * Check if shipping and billing addresses adds or not adds to customer address book by different conditions
     *
     * @case 1 POSITIVE saveInAddressBook for billing and shipping checked, shippingSameAsBilling false
     * @case 2 POSITIVE shippingSameAsBilling true
     * @case 3 POSITIVE customer haven't default shipping and billing addresses
     * @case 4 POSITIVE customer address haven't phone number (from Paypal) - not saving addresses
     *
     * @return array
     */
    public function prepareRegisteredCustomerQuoteDataProvider()
    {
        return [
            [
                'conditions' => [
                    'isTelephone' => true,
                    'isShippingSameAsBilling' => false,
                    'isShippingSaveInAddressBook' => true,
                    'isBillingSaveInAddressBook' => true,
                    'isDefaultShipping' => true,
                    'isDefaultBilling' => true,
                ],
                'exportBillingCustomerAddressCalls' => 1,
                'exportShippingCustomerAddressCalls' => 1,
            ],
            [
                'conditions' => [
                    'isTelephone' => true,
                    'isShippingSameAsBilling' => true,
                    'isShippingSaveInAddressBook' => false,
                    'isBillingSaveInAddressBook' => true,
                    'isDefaultShipping' => true,
                    'isDefaultBilling' => true,
                ],
                'exportBillingCustomerAddressCalls' => 1,
                'exportShippingCustomerAddressCalls' => 0,
            ],
            [
                'conditions' => [
                    'isTelephone' => true,
                    'isShippingSameAsBilling' => false,
                    'isShippingSaveInAddressBook' => false,
                    'isBillingSaveInAddressBook' => false,
                    'isDefaultShipping' => false,
                    'isDefaultBilling' => false,
                ],
                'exportBillingCustomerAddressCalls' => 1,
                'exportShippingCustomerAddressCalls' => 1,
            ],
            [
                'conditions' => [
                    'isTelephone' => false,
                    'isShippingSameAsBilling' => false,
                    'isShippingSaveInAddressBook' => false,
                    'isBillingSaveInAddressBook' => false,
                    'isDefaultShipping' => false,
                    'isDefaultBilling' => false,
                ],
                'exportBillingCustomerAddressCalls' => 0,
                'exportShippingCustomerAddressCalls' => 0,
            ],

        ];
    }
}
