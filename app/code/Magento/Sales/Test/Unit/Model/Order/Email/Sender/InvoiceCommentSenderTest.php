<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Payment\Helper\Data;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity;
use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use Magento\Sales\Model\ResourceModel\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;

class InvoiceCommentSenderTest extends AbstractSenderTest
{
    /**
     * @var InvoiceCommentSender
     */
    protected $sender;

    /**
     * @var MockObject
     */
    protected $invoiceMock;

    protected function setUp(): void
    {
        $this->stepMockSetup();
        $this->paymentHelper = $this->createPartialMock(Data::class, ['getInfoBlockHtml']);

        $this->stepIdentityContainerInit(InvoiceCommentIdentity::class);

        $this->addressRenderer->expects($this->any())->method('format')->willReturn(1);

        $this->invoiceMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Invoice::class,
            ['getStore', 'getOrder']
        );
        $this->invoiceMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->invoiceMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->sender = new InvoiceCommentSender(
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
        $this->stepAddressFormat($this->addressMock);
        $result = $this->sender->send($this->invoiceMock);
        $this->assertFalse($result);
    }

    public function testSendTrueWithoutCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $this->stepAddressFormat($billingAddress);
        $comment = 'comment_test';
        $customerName = 'Test Customer';
        $frontendStatusLabel = 'Processing';
        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->willReturn(false);

        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);

        $this->orderMock->expects($this->any())
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
                    'invoice' => $this->invoiceMock,
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

        $this->stepSendWithoutSendCopy();
        $result = $this->sender->send($this->invoiceMock, true, $comment);
        $this->assertTrue($result);
    }

    public function testSendTrueWithCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $customerName = 'Test Customer';
        $frontendStatusLabel = 'Processing';
        $this->stepAddressFormat($billingAddress);
        $comment = 'comment_test';
        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->willReturn(false);

        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);

        $this->orderMock->expects($this->any())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);

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
                    'invoice' => $this->invoiceMock,
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
        $this->stepSendWithCallSendCopyTo();
        $result = $this->sender->send($this->invoiceMock, false, $comment);
        $this->assertTrue($result);
    }

    public function testSendVirtualOrder()
    {
        $isVirtualOrder = true;
        $this->orderMock->setData(OrderInterface::IS_VIRTUAL, $isVirtualOrder);
        $this->stepAddressFormat($this->addressMock, $isVirtualOrder);
        $customerName = 'Test Customer';
        $frontendStatusLabel = 'Complete';

        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);

        $this->orderMock->expects($this->any())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                [
                    'order' => $this->orderMock,
                    'invoice' => $this->invoiceMock,
                    'billing' => $this->addressMock,
                    'comment' => '',
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => null,
                    'formattedBillingAddress' => 1,
                    'order_data' => [
                        'customer_name' => $customerName,
                        'frontend_status_label' => $frontendStatusLabel
                    ]
                ]
            );
        $this->assertFalse($this->sender->send($this->invoiceMock));
    }
}
