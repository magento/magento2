<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

class GuestShippingInformationManagementTest extends \PHPUnit\Framework\TestCase
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
        $this->quoteIdMaskFactoryMock = $this->createPartialMock(
            \Magento\Quote\Model\QuoteIdMaskFactory::class,
            ['create']
        );
        $this->shippingInformationManagementMock = $this->createMock(
            \Magento\Checkout\Api\ShippingInformationManagementInterface::class
        );

        $this->model = $objectManager->getObject(
            \Magento\Checkout\Model\GuestShippingInformationManagement::class,
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
        $addressInformationMock = $this->createMock(\Magento\Checkout\Api\Data\ShippingInformationInterface::class);

        $quoteIdMaskMock = $this->createPartialMock(\Magento\Quote\Model\QuoteIdMask::class, ['load', 'getQuoteId']);
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($quoteIdMaskMock);

        $quoteIdMaskMock->expects($this->once())->method('load')->with($cartId, 'masked_id')->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('getQuoteId')->willReturn($quoteId);

        $paymentInformationMock = $this->createMock(\Magento\Checkout\Api\Data\PaymentDetailsInterface::class);
        $this->shippingInformationManagementMock->expects($this->once())
            ->method('saveAddressInformation')
            ->with($quoteId, $addressInformationMock)
            ->willReturn($paymentInformationMock);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }
}
