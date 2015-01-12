<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

class CreditmemoSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
     */
    protected $sender;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $senderBuilderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateContainerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $identityContainerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoResource;

    protected function setUp()
    {
        $this->senderBuilderFactoryMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\SenderBuilderFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->templateContainerMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Container\Template',
            ['setTemplateVars'],
            [],
            '',
            false
        );
        $this->paymentHelper = $this->getMock('\Magento\Payment\Helper\Data', ['getInfoBlockHtml'], [], '', false);
        $this->paymentHelper->expects($this->any())
            ->method('getInfoBlockHtml')
            ->will($this->returnValue('payment'));

        $this->creditmemoResource = $this->getMock(
            '\Magento\Sales\Model\Resource\Order\Creditmemo',
            [],
            [],
            '',
            false
        );

        $this->storeMock = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getStoreId', '__wakeup'],
            [],
            '',
            false
        );

        $this->identityContainerMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity',
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId'],
            [],
            '',
            false
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->orderMock = $this->getMock(
            '\Magento\Sales\Model\Order',
            [
                'getStore', 'getBillingAddress', 'getPayment',
                '__wakeup', 'getCustomerIsGuest', 'getCustomerName',
                'getCustomerEmail'
            ],
            [],
            '',
            false
        );

        $this->orderMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
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

        $this->creditmemoMock = $this->getMock(
            '\Magento\Sales\Model\Order\Creditmemo',
            ['getStore', '__wakeup', 'getOrder'],
            [],
            '',
            false
        );
        $this->creditmemoMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->creditmemoMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($this->orderMock));

        $this->sender = new CreditmemoSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->paymentHelper,
            $this->creditmemoResource
        );
    }

    public function testSendFalse()
    {
        $result = $this->sender->send($this->creditmemoMock);
        $this->assertFalse($result);
    }

    public function testSendTrueWithCustomerCopy()
    {
        $billingAddress = 'billing_address';
        $comment = 'comment_test';

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($billingAddress));

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'invoice' => $this->creditmemoMock,
                        'comment' => $comment,
                        'billing' => $billingAddress,
                        'payment_html' => 'payment',
                        'store' => $this->storeMock,
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

        $senderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender',
            ['send', 'sendCopyTo'],
            [],
            '',
            false
        );
        $senderMock->expects($this->once())
            ->method('send');
        $senderMock->expects($this->never())
            ->method('sendCopyTo');

        $this->senderBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($senderMock));

        $result = $this->sender->send($this->creditmemoMock, true, $comment);
        $this->assertTrue($result);
    }

    public function testSendTrueWithoutCustomerCopy()
    {
        $billingAddress = 'billing_address';
        $comment = 'comment_test';

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($billingAddress));

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'invoice' => $this->creditmemoMock,
                        'billing' => $billingAddress,
                        'payment_html' => 'payment',
                        'comment' => $comment,
                        'store' => $this->storeMock,
                    ]
                )
            );
        $senderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender',
            ['send', 'sendCopyTo'],
            [],
            '',
            false
        );
        $senderMock->expects($this->never())
            ->method('send');
        $senderMock->expects($this->once())
            ->method('sendCopyTo');

        $this->senderBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($senderMock));

        $result = $this->sender->send($this->creditmemoMock, false, $comment);
        $this->assertTrue($result);
    }
}
