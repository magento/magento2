<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AddressDetailsManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\AddressDetailsManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingMethodManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressDetailsFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProcessor;

    /** @var \Magento\Quote\Model\QuoteRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->billingAddressManagement = $this->getMock('Magento\Quote\Api\BillingAddressManagementInterface');
        $this->shippingAddressManagement = $this->getMock('Magento\Quote\Api\ShippingAddressManagementInterface');
        $this->paymentMethodManagement = $this->getMock('Magento\Quote\Api\PaymentMethodManagementInterface');
        $this->shippingMethodManagement = $this->getMock('Magento\Quote\Api\ShippingMethodManagementInterface');
        $this->addressDetailsFactory = $this->getMock(
            'Magento\Quote\Model\AddressDetailsFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->dataProcessor = $this->getMock('Magento\Quote\Model\AddressAdditionalDataProcessor', [], [], '', false);
        $this->quoteRepository = $this->getMock('Magento\Quote\Model\QuoteRepository', [], [], '', false);

        $this->model = $this->objectManager->getObject(
            'Magento\Quote\Model\AddressDetailsManagement',
            [
                'billingAddressManagement' => $this->billingAddressManagement,
                'shippingAddressManagement' => $this->shippingAddressManagement,
                'paymentMethodManagement' => $this->paymentMethodManagement,
                'shippingMethodManagement' => $this->shippingMethodManagement,
                'addressDetailsFactory' => $this->addressDetailsFactory,
                'dataProcessor' => $this->dataProcessor,
                'quoteRepository' => $this->quoteRepository,
            ]
        );
    }

    public function testSaveAddresses()
    {
        $cartId = 100;
        $additionalData = $this->getMock('\Magento\Quote\Api\Data\AddressAdditionalDataInterface');
        $billingAddressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', [], [], '', false);
        $shippingAddressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', [], [], '', false);

        $this->billingAddressManagement->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock)
            ->willReturn(1);

        $billingAddressMock->expects($this->once())->method('format')->with('html');
        $this->billingAddressManagement->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($billingAddressMock);

        $this->shippingAddressManagement->expects($this->once())
            ->method('assign')
            ->with($cartId, $shippingAddressMock)
            ->willReturn(1);

        $shippingAddressMock->expects($this->once())->method('format')->with('html');
        $this->shippingAddressManagement->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($shippingAddressMock);

        $shippingMethodMock = $this->getMock('\Magento\Quote\Api\Data\ShippingMethodInterface');
        $this->shippingMethodManagement->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$shippingMethodMock]);
        $paymentMethodMock = $this->getMock('\Magento\Quote\Api\Data\PaymentMethodInterface');
        $this->paymentMethodManagement->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$paymentMethodMock]);

        $addressDetailsMock = $this->getMock('\Magento\Quote\Model\AddressDetails', [], [], '', false);
        $this->addressDetailsFactory->expects($this->once())->method('create')->willReturn($addressDetailsMock);

        $addressDetailsMock->expects($this->once())
            ->method('setShippingMethods')
            ->with([$shippingMethodMock])
            ->willReturnSelf();
        $addressDetailsMock->expects($this->once())
            ->method('setPaymentMethods')
            ->with([$paymentMethodMock])
            ->willReturnSelf();
        $this->dataProcessor->expects($this->once())->method('process')->with($additionalData);

        $quote = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $quote->expects($this->once())
            ->method('setCheckoutMethod')
            ->willReturnSelf();

        $this->quoteRepository
            ->expects($this->once())
            ->method('getActive')
            ->willReturn($quote);

        $this->model->saveAddresses($cartId, $billingAddressMock, $shippingAddressMock, $additionalData, 'register');
    }
}
