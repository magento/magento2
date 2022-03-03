<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity;
use Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender;
use Magento\Sales\Model\Order\Shipment;
use PHPUnit\Framework\MockObject\MockObject;

class ShipmentCommentSenderTest extends AbstractSenderTest
{
    /**
     * @var ShipmentCommentSender
     */
    protected $sender;

    /**
     * @var MockObject
     */
    protected $shipmentMock;

    protected function setUp(): void
    {
        $this->stepMockSetup();
        $this->stepIdentityContainerInit(ShipmentCommentIdentity::class);
        $this->addressRenderer->expects($this->any())->method('format')->willReturn(1);
        $this->shipmentMock = $this->createPartialMock(
            Shipment::class,
            ['getStore', 'getOrder']
        );
        $this->shipmentMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->shipmentMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->sender = new ShipmentCommentSender(
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
        $result = $this->sender->send($this->shipmentMock);
        $this->assertFalse($result);
    }

    public function testSendTrueWithoutCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';
        $customerName = 'Test Customer';
        $frontendStatusLabel = 'Processing';
        $isNotVirtual = true;

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->willReturn(false);
        $this->stepAddressFormat($billingAddress);

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->orderMock->expects($this->any())
            ->method('getIsNotVirtual')
            ->willReturn($isNotVirtual);
        $this->orderMock->expects($this->once())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                [
                    'order' => $this->orderMock,
                    'shipment' => $this->shipmentMock,
                    'billing' => $billingAddress,
                    'comment' => $comment,
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => 1,
                    'formattedBillingAddress' => 1,
                    'order_data' => [
                        'customer_name' => $customerName,
                        'frontend_status_label' => $frontendStatusLabel,
                        'is_not_virtual' => $isNotVirtual,
                    ]
                ]
            );
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->stepSendWithoutSendCopy();
        $result = $this->sender->send($this->shipmentMock, true, $comment);
        $this->assertTrue($result);
    }

    public function testSendTrueWithCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';
        $customerName = 'Test Customer';
        $frontendStatusLabel = 'Processing';
        $isNotVirtual = true;

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
        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->orderMock->expects($this->any())
            ->method('getIsNotVirtual')
            ->willReturn($isNotVirtual);
        $this->orderMock->expects($this->once())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                [
                    'order' => $this->orderMock,
                    'shipment' => $this->shipmentMock,
                    'billing' => $billingAddress,
                    'comment' => $comment,
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => 1,
                    'formattedBillingAddress' => 1,
                    'order_data' => [
                        'customer_name' => $customerName,
                        'frontend_status_label' => $frontendStatusLabel,
                        'is_not_virtual' => $isNotVirtual,
                    ]
                ]
            );
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->stepSendWithCallSendCopyTo();
        $result = $this->sender->send($this->shipmentMock, false, $comment);
        $this->assertTrue($result);
    }

    public function testSendVirtualOrder()
    {
        $isVirtualOrder = true;
        $this->orderMock->setData(OrderInterface::IS_VIRTUAL, $isVirtualOrder);
        $this->stepAddressFormat($this->addressMock, $isVirtualOrder);
        $customerName = 'Test Customer';
        $frontendStatusLabel = 'Complete';
        $isNotVirtual = false;

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);
        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->orderMock->expects($this->any())
            ->method('getIsNotVirtual')
            ->willReturn($isNotVirtual);
        $this->orderMock->expects($this->once())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                [
                    'order' => $this->orderMock,
                    'shipment' => $this->shipmentMock,
                    'billing' => $this->addressMock,
                    'comment' => '',
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => null,
                    'formattedBillingAddress' => 1,
                    'order_data' => [
                        'customer_name' => $customerName,
                        'frontend_status_label' => $frontendStatusLabel,
                        'is_not_virtual' => $isNotVirtual
                    ]

                ]
            );
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->assertFalse($this->sender->send($this->shipmentMock));
    }
}
