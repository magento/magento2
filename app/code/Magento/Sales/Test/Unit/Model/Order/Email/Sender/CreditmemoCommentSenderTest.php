<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;

class CreditmemoCommentSenderTest extends AbstractSenderTest
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender
     */
    protected $sender;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $creditmemoMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->stepMockSetup();
        $this->stepIdentityContainerInit(\Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity::class);
        $this->addressRenderer->expects($this->any())->method('format')->willReturn(1);
        $this->creditmemoMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Creditmemo::class,
            ['getStore', '__wakeup', 'getOrder']
        );
        $this->creditmemoMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->creditmemoMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->sender = new CreditmemoCommentSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRenderer,
            $this->eventManagerMock
        );
    }

    public function testSendFalse()
    {
        $billingAddress = $this->addressMock;
        $this->stepAddressFormat($billingAddress);
        $result = $this->sender->send($this->creditmemoMock);
        $this->assertFalse($result);
    }

    public function testSendVirtualOrder()
    {
        $this->orderMock->setData(\Magento\Sales\Api\Data\OrderInterface::IS_VIRTUAL, true);
        $billingAddress = $this->addressMock;
        $customerName = 'test customer';
        $frontendStatusLabel = 'Complete';

        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->orderMock->expects($this->once())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);

        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'creditmemo' => $this->creditmemoMock,
                        'comment' => '',
                        'billing' => $billingAddress,
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => null,
                        'formattedBillingAddress' => 1,
                        'order_data' => [
                            'customer_name' => $customerName,
                            'frontend_status_label' => $frontendStatusLabel
                        ]
                    ]
                )
            );
        $this->stepAddressFormat($billingAddress, true);
        $result = $this->sender->send($this->creditmemoMock);
        $this->assertFalse($result);
    }

    public function testSendTrueWithCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';
        $customerName = 'test customer';
        $frontendStatusLabel = 'Complete';

        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->orderMock->expects($this->once())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->willReturn(false);
        $this->stepAddressFormat($billingAddress);
        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'creditmemo' => $this->creditmemoMock,
                        'comment' => $comment,
                        'billing' => $billingAddress,
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => 1,
                        'formattedBillingAddress' => 1,
                        'order_data' => [
                            'customer_name' => $customerName,
                            'frontend_status_label' => $frontendStatusLabel
                        ]
                    ]
                )
            );
        $this->stepSendWithoutSendCopy();
        $result = $this->sender->send($this->creditmemoMock, true, $comment);
        $this->assertTrue($result);
    }

    public function testSendTrueWithoutCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';
        $customerName = 'test customer';
        $frontendStatusLabel = 'Complete';

        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->orderMock->expects($this->once())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->willReturn(false);
        $this->stepAddressFormat($billingAddress);
        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'creditmemo' => $this->creditmemoMock,
                        'billing' => $billingAddress,
                        'comment' => $comment,
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => 1,
                        'formattedBillingAddress' => 1,
                        'order_data' => [
                            'customer_name' => $customerName,
                            'frontend_status_label' => $frontendStatusLabel
                        ]
                    ]
                )
            );
        $this->stepSendWithCallSendCopyTo();
        $result = $this->sender->send($this->creditmemoMock, false, $comment);
        $this->assertTrue($result);
    }
}
