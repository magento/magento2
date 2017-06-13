<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Test\Unit\Model;

use \Magento\Quote\Model\ShippingAddressManagement;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingAddressManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingAddressManagement
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsCollectorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $amountErrorMessageMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->quoteAddressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            [
                'setSameAsBilling',
                'setCollectShippingRates',
                '__wakeup',
                'collectTotals',
                'save',
                'getId',
                'getCustomerAddressId',
                'getSaveInAddressBook',
                'getSameAsBilling',
                'importCustomerAddressData',
                'setSaveInAddressBook',
            ],
            [],
            '',
            false
        );
        $this->validatorMock = $this->getMock(
            \Magento\Quote\Model\QuoteAddressValidator::class, [], [], '', false
        );
        $this->totalsCollectorMock = $this->getMock(
            \Magento\Quote\Model\Quote\TotalsCollector::class,
            [],
            [],
            '',
            false
        );
        $this->addressRepository = $this->getMock(\Magento\Customer\Api\AddressRepositoryInterface::class);

        $this->amountErrorMessageMock = $this->getMock(
            \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage::class,
            ['getMessage'],
            [],
            '',
            false
        );

        $this->service = $this->objectManager->getObject(
            \Magento\Quote\Model\ShippingAddressManagement::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'addressValidator' => $this->validatorMock,
                'logger' => $this->getMock(\Psr\Log\LoggerInterface::class),
                'scopeConfig' => $this->scopeConfigMock,
                'totalsCollector' => $this->totalsCollectorMock,
                'addressRepository' => $this->addressRepository
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expected ExceptionMessage error345
     */
    public function testSetAddressValidationFailed()
    {
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cart654')
            ->will($this->returnValue($quoteMock));

        $this->validatorMock->expects($this->once())->method('validate')
            ->will($this->throwException(new \Magento\Framework\Exception\NoSuchEntityException(__('error345'))));

        $this->service->assign('cart654', $this->quoteAddressMock);
    }

    public function testSetAddress()
    {
        $addressId = 1;
        $customerAddressId = 150;

        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getIsMultiShipping', 'isVirtual', 'validateMinimumAmount', 'setShippingAddress', 'getShippingAddress'],
            [],
            '',
            false
        );
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cart867')
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($this->quoteAddressMock)
            ->willReturnSelf();

        $this->quoteAddressMock->expects($this->once())->method('getSaveInAddressBook')->willReturn(1);
        $this->quoteAddressMock->expects($this->once())->method('getSameAsBilling')->willReturn(1);
        $this->quoteAddressMock->expects($this->once())->method('getCustomerAddressId')->willReturn($customerAddressId);

        $customerAddressMock = $this->getMock(\Magento\Customer\Api\Data\AddressInterface::class);

        $this->addressRepository->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $this->validatorMock->expects($this->once())->method('validate')
            ->with($this->quoteAddressMock)
            ->willReturn(true);

        $quoteMock->expects($this->exactly(3))->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();

        $this->quoteAddressMock->expects($this->once())->method('setSameAsBilling')->with(1)->willReturnSelf();
        $this->quoteAddressMock->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();
        $this->quoteAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();

        $this->quoteAddressMock->expects($this->once())->method('save')->willReturnSelf();
        $this->quoteAddressMock->expects($this->once())->method('getId')->will($this->returnValue($addressId));

        $this->assertEquals($addressId, $this->service->assign('cart867', $this->quoteAddressMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart contains virtual product(s) only. Shipping address is not applicable
     */
    public function testSetAddressForVirtualProduct()
    {
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cart867')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(true));
        $quoteMock->expects($this->never())->method('setShippingAddress');

        $this->quoteAddressMock->expects($this->never())->method('getCustomerAddressId');
        $this->quoteAddressMock->expects($this->never())->method('setSaveInAddressBook');

        $quoteMock->expects($this->never())->method('save');

        $this->service->assign('cart867', $this->quoteAddressMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Unable to save address. Please check input data.
     */
    public function testSetAddressWithInabilityToSaveQuote()
    {
        $this->quoteAddressMock->expects($this->once())->method('save')->willThrowException(
            new \Exception('Unable to save address. Please check input data.')
        );

        $customerAddressId = 150;

        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getIsMultiShipping', 'isVirtual', 'validateMinimumAmount', 'setShippingAddress', 'getShippingAddress'],
            [],
            '',
            false
        );
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cart867')
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($this->quoteAddressMock)
            ->willReturnSelf();

        $customerAddressMock = $this->getMock(\Magento\Customer\Api\Data\AddressInterface::class);

        $this->addressRepository->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $this->validatorMock->expects($this->once())->method('validate')
            ->with($this->quoteAddressMock)
            ->willReturn(true);

        $this->quoteAddressMock->expects($this->once())->method('getSaveInAddressBook')->willReturn(1);
        $this->quoteAddressMock->expects($this->once())->method('getSameAsBilling')->willReturn(1);
        $this->quoteAddressMock->expects($this->once())->method('getCustomerAddressId')->willReturn($customerAddressId);

        $quoteMock->expects($this->exactly(2))->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();

        $this->quoteAddressMock->expects($this->once())->method('setSameAsBilling')->with(1)->willReturnSelf();
        $this->quoteAddressMock->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();
        $this->quoteAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();

        $this->service->assign('cart867', $this->quoteAddressMock);
    }

    public function testGetAddress()
    {
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with('cartId')->will(
            $this->returnValue($quoteMock)
        );

        $addressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $quoteMock->expects($this->any())->method('getShippingAddress')->will($this->returnValue($addressMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(false));
        $this->assertEquals($addressMock, $this->service->get('cartId'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cart contains virtual product(s) only. Shipping address is not applicable
     */
    public function testGetAddressOfQuoteWithVirtualProducts()
    {
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with('cartId')->will(
            $this->returnValue($quoteMock)
        );

        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(true));
        $quoteMock->expects($this->never())->method('getShippingAddress');

        $this->service->get('cartId');
    }
}
