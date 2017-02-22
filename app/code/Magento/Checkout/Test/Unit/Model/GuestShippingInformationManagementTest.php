<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

class GuestShippingInformationManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingInformationManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \Magento\Checkout\Model\GuestShippingInformationManagement
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteIdMaskFactoryMock = $this->getMock(
            '\Magento\Quote\Model\QuoteIdMaskFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->shippingInformationManagementMock = $this->getMock(
            '\Magento\Checkout\Api\ShippingInformationManagementInterface'
        );

        $this->model = $objectManager->getObject(
            'Magento\Checkout\Model\GuestShippingInformationManagement',
            [
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                'shippingInformationManagement' => $this->shippingInformationManagementMock
            ]
        );
    }

    public function testSaveAddressInformation()
    {
        $cartId = 'masked_id';
        $quoteId = 100;
        $addressInformationMock = $this->getMock('\Magento\Checkout\Api\Data\ShippingInformationInterface');

        $quoteIdMaskMock = $this->getMock('\Magento\Quote\Model\QuoteIdMask', ['load', 'getQuoteId'], [], '', false);
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($quoteIdMaskMock);

        $quoteIdMaskMock->expects($this->once())->method('load')->with($cartId, 'masked_id')->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('getQuoteId')->willReturn($quoteId);

        $paymentInformationMock = $this->getMock('\Magento\Checkout\Api\Data\PaymentDetailsInterface');
        $this->shippingInformationManagementMock->expects($this->once())
            ->method('saveAddressInformation')
            ->with($quoteId, $addressInformationMock)
            ->willReturn($paymentInformationMock);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }
}
