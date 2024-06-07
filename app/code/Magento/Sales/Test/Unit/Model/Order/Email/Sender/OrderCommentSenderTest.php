<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

class OrderCommentSenderTest extends AbstractSenderTestCase
{
    /**
     * @var OrderCommentSender
     */
    protected $sender;

    protected function setUp(): void
    {
        $this->stepMockSetup();
        $this->stepIdentityContainerInit(OrderCommentIdentity::class);
        $this->addressRenderer->expects($this->any())->method('format')->willReturn(1);
        $this->sender = new OrderCommentSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRenderer,
            $this->eventManagerMock,
            $this->appEmulator
        );
    }

    public function testSendFalse()
    {
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->stepAddressFormat($this->addressMock);
        $result = $this->sender->send($this->orderMock);
        $this->assertFalse($result);
    }

    public function testSendTrue()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';
        $customerName='Test Customer';
        $frontendStatusLabel='Processing';
        $this->stepAddressFormat($billingAddress);
        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->willReturn(false);
        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->orderMock->expects($this->once())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                [
                    'order' => $this->orderMock,
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
            );
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->stepSendWithoutSendCopy();
        $result = $this->sender->send($this->orderMock, true, $comment);
        $this->assertTrue($result);
    }

    public function testSendVirtualOrder()
    {
        $isVirtualOrder = true;
        $this->orderMock->setData(OrderInterface::IS_VIRTUAL, $isVirtualOrder);
        $this->stepAddressFormat($this->addressMock, $isVirtualOrder);
        $customerName='Test Customer';
        $frontendStatusLabel='Complete';

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);
        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->orderMock->expects($this->once())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                [
                    'order' => $this->orderMock,
                    'comment' => '',
                    'billing' => $this->addressMock,
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => null,
                    'formattedBillingAddress' => 1,
                    'order_data' => [
                        'customer_name' => $customerName,
                        'frontend_status_label' => $frontendStatusLabel
                    ]
                ]
            );
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->assertFalse($this->sender->send($this->orderMock));
    }
}
