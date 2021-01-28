<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;

class InvoiceCommentSenderTest extends AbstractSenderTest
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender
     */
    protected $sender;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $invoiceMock;

    protected function setUp(): void
    {
        $this->stepMockSetup();
        $this->paymentHelper = $this->createPartialMock(\Magento\Payment\Helper\Data::class, ['getInfoBlockHtml']);

        $this->invoiceResource = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Invoice::class);

        $this->stepIdentityContainerInit(\Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity::class);

        $this->addressRenderer->expects($this->any())->method('format')->willReturn(1);

        $this->invoiceMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Invoice::class,
            ['getStore', '__wakeup', 'getOrder']
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

    public function testSendTrueWithCustomerCopy()
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
                $this->equalTo(
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
                )
            );

        $this->stepSendWithoutSendCopy();
        $result = $this->sender->send($this->invoiceMock, true, $comment);
        $this->assertTrue($result);
    }

    public function testSendTrueWithoutCustomerCopy()
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
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
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
                )
            );
        $this->stepSendWithCallSendCopyTo();
        $result = $this->sender->send($this->invoiceMock, false, $comment);
        $this->assertTrue($result);
    }

    public function testSendVirtualOrder()
    {
        $isVirtualOrder = true;
        $this->orderMock->setData(\Magento\Sales\Api\Data\OrderInterface::IS_VIRTUAL, $isVirtualOrder);
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
                $this->equalTo(
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
                )
            );
        $this->assertFalse($this->sender->send($this->invoiceMock));
    }
}
