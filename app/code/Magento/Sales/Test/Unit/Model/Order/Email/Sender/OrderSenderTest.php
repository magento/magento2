<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use \Magento\Sales\Model\Order\Email\Sender\OrderSender;

class OrderSenderTest extends AbstractSenderTest
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $sender;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderResource;

    protected function setUp()
    {
        $this->stepMockSetup();
        $this->paymentHelper = $this->getMock(
            '\Magento\Payment\Helper\Data',
            ['getInfoBlockHtml'],
            [],
            '',
            false
        );
        $this->paymentHelper->expects($this->any())
            ->method('getInfoBlockHtml')
            ->will($this->returnValue('payment'));

        $this->orderResource = $this->getMock(
            '\Magento\Sales\Model\Resource\Order',
            [],
            [],
            '',
            false
        );
        $this->stepIdentityContainerInit('\Magento\Sales\Model\Order\Email\Container\OrderIdentity');

        $this->sender = new OrderSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->paymentHelper,
            $this->orderResource,
            $this->addressRendererMock
        );
    }

    public function testSendFalse()
    {
        $this->stepAddressFormat($this->addressMock);
        $result = $this->sender->send($this->orderMock);
        $this->assertFalse($result);
    }

    public function testSendTrueForCustomer()
    {
        $billingAddress = $this->addressMock;

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));
        $this->stepAddressFormat($billingAddress);

        $paymentInfoMock = $this->getMock(
            '\Magento\Payment\Model\Info',
            [],
            [],
            '',
            false
        );
        $this->orderMock->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($paymentInfoMock));

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'billing' => $billingAddress,
                        'payment_html' => 'payment',
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => 1,
                        'formattedBillingAddress' => 1
                    ]
                )
            );
        $this->stepSend($this->once(), $this->once());
        $result = $this->sender->send($this->orderMock);
        $this->assertTrue($result);
    }

    public function testSendTrueForGuest()
    {
        $billingAddress = $this->getMock(
            '\Magento\Sales\Model\Order\Address',
            [],
            [],
            '',
            false
        );
        $this->stepAddressFormat($billingAddress);
        $billingAddress->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('name'));
        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(true));
        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($billingAddress));

        $paymentInfoMock = $this->getMock(
            '\Magento\Payment\Model\Info',
            [],
            [],
            '',
            false
        );
        $this->orderMock->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($paymentInfoMock));

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'billing' => $billingAddress,
                        'payment_html' => 'payment',
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => 1,
                        'formattedBillingAddress' => 1
                    ]
                )
            );
        $this->stepSend($this->once(), $this->once());
        $result = $this->sender->send($this->orderMock);
        $this->assertTrue($result);
    }
}
