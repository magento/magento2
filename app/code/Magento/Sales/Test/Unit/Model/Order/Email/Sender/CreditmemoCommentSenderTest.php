<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;
use PHPUnit\Framework\MockObject\MockObject;

class CreditmemoCommentSenderTest extends AbstractSenderTest
{
    /**
     * @var CreditmemoCommentSender
     */
    protected $sender;

    /**
     * @var MockObject
     */
    protected $creditmemoMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->stepMockSetup();
        $this->stepIdentityContainerInit(CreditmemoCommentIdentity::class);
        $this->addressRenderer->expects($this->any())->method('format')->willReturn(1);
        $this->creditmemoMock = $this->createPartialMock(
            Creditmemo::class,
            ['getStore', 'getOrder']
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
            $this->eventManagerMock,
            $this->appEmulator
        );
    }

    public function testSendFalse()
    {
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $billingAddress = $this->addressMock;
        $this->stepAddressFormat($billingAddress);
        $result = $this->sender->send($this->creditmemoMock);
        $this->assertFalse($result);
    }

    public function testSendVirtualOrder()
    {
        $this->orderMock->setData(OrderInterface::IS_VIRTUAL, true);
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
            );
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->stepAddressFormat($billingAddress, true);
        $result = $this->sender->send($this->creditmemoMock);
        $this->assertFalse($result);
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
            );
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->stepSendWithoutSendCopy();
        $result = $this->sender->send($this->creditmemoMock, true, $comment);
        $this->assertTrue($result);
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
        $this->identityContainerMock->expects($this->once())
            ->method('getCopyMethod')
            ->willReturn('copy');
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
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
            );
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->stepSendWithCallSendCopyTo();
        $result = $this->sender->send($this->creditmemoMock, false, $comment);
        $this->assertTrue($result);
    }
}
