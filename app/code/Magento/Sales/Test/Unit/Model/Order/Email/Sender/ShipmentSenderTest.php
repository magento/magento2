<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use \Magento\Sales\Model\Order\Email\Sender\ShipmentSender;

class ShipmentSenderTest extends AbstractSenderTest
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $sender;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentResource;

    protected function setUp()
    {
        $this->stepMockSetup();
        $this->paymentHelper = $this->getMock('\Magento\Payment\Helper\Data', ['getInfoBlockHtml'], [], '', false);
        $this->paymentHelper->expects($this->any())
            ->method('getInfoBlockHtml')
            ->will($this->returnValue('payment'));

        $this->shipmentResource = $this->getMock(
            '\Magento\Sales\Model\Resource\Order\Shipment',
            [],
            [],
            '',
            false
        );

        $this->stepIdentityContainerInit('\Magento\Sales\Model\Order\Email\Container\ShipmentIdentity');

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

        $this->shipmentMock = $this->getMock(
            '\Magento\Sales\Model\Order\Shipment',
            ['getStore', '__wakeup', 'getOrder'],
            [],
            '',
            false
        );
        $this->shipmentMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->shipmentMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($this->orderMock));

        $this->sender = new ShipmentSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->paymentHelper,
            $this->shipmentResource,
            $this->addressRendererMock
        );
    }

    public function testSendFalse()
    {
        $this->stepAddressFormat($this->addressMock);
        $result = $this->sender->send($this->shipmentMock);
        $this->assertFalse($result);
    }

    public function testSendTrueWithCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));
        $this->stepAddressFormat($billingAddress);

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'shipment' => $this->shipmentMock,
                        'comment' => $comment,
                        'billing' => $billingAddress,
                        'payment_html' => 'payment',
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => 1,
                        'formattedBillingAddress' => 1
                    ]
                )
            );
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
        $this->stepSendWithoutSendCopy();
        $result = $this->sender->send($this->shipmentMock, true, $comment);
        $this->assertTrue($result);
    }

    public function testSendTrueWithoutCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));
        $this->stepAddressFormat($billingAddress);

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'shipment' => $this->shipmentMock,
                        'billing' => $billingAddress,
                        'payment_html' => 'payment',
                        'comment' => $comment,
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => 1,
                        'formattedBillingAddress' => 1
                    ]
                )
            );
        $this->stepSendWithCallSendCopyTo();
        $result = $this->sender->send($this->shipmentMock, false, $comment);
        $this->assertTrue($result);
    }
}
