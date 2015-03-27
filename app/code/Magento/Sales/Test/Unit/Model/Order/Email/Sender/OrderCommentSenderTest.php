<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

class OrderCommentSenderTest extends AbstractSenderTest
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
     */
    protected $sender;

    protected function setUp()
    {
        $this->stepMockSetup();
        $this->stepIdentityContainerInit('\Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity');
        $this->sender = new OrderCommentSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->addressRendererMock
        );
    }

    public function testSendFalse()
    {
        $this->stepAddressFormat($this->addressMock);
        $result = $this->sender->send($this->orderMock);
        $this->assertFalse($result);
    }

    public function testSendTrue()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';
        $this->stepAddressFormat($billingAddress);
        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));


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
                        'comment' => $comment,
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => 1,
                        'formattedBillingAddress' => 1
                    ]
                )
            );
        $this->stepSendWithoutSendCopy();
        $result = $this->sender->send($this->orderMock, true, $comment);
        $this->assertTrue($result);
    }
}
