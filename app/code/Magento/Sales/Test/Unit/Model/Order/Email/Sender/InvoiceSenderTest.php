<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for Magento\Sales\Model\Order\Email\Sender\InvoiceSender class.
 */
class InvoiceSenderTest extends AbstractSenderTest
{
    private const INVOICE_ID = 1;

    private const ORDER_ID = 1;

    /**
     * @var InvoiceSender
     */
    protected $sender;

    /**
     * @var Invoice|MockObject
     */
    protected $invoiceMock;

    /**
     * @var EntityAbstract|MockObject
     */
    protected $invoiceResourceMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->stepMockSetup();

        $this->invoiceResourceMock = $this->createPartialMock(
            InvoiceResource::class,
            ['saveAttribute']
        );

        $this->invoiceMock = $this->getMockBuilder(Invoice::class)
            ->addMethods(['setSendEmail', 'getCustomerNoteNotify', 'getCustomerNote'])
            ->onlyMethods(['getStore', 'getId', 'getOrder', 'setEmailSent'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->invoiceMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->invoiceMock->method('getId')
            ->willReturn(self::INVOICE_ID);
        $this->orderMock->method('getId')
            ->willReturn(self::ORDER_ID);

        $this->identityContainerMock = $this->createPartialMock(
            InvoiceIdentity::class,
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId', 'getCopyMethod']
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->sender = new InvoiceSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRenderer,
            $this->paymentHelper,
            $this->invoiceResourceMock,
            $this->globalConfig,
            $this->eventManagerMock,
            $this->appEmulator
        );
    }

    /**
     * @param int $configValue
     * @param int|null $forceSyncMode
     * @param int|null $customerNoteNotify
     * @param bool|null $emailSendingResult
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @dataProvider sendDataProvider
     */
    public function testSend(
        int $configValue,
        ?int $forceSyncMode,
        ?int $customerNoteNotify,
        ?bool $emailSendingResult
    ): void {
        $comment = 'comment_test';
        $address = 'address_test';
        $configPath = 'sales_email/general/async_sending';
        $customerName = 'Test Customer';
        $isNotVirtual = true;
        $frontendStatusLabel = 'Processing';

        $this->invoiceMock->expects($this->once())
            ->method('setSendEmail')
            ->with($emailSendingResult);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with($configPath)
            ->willReturn($configValue);

        if (!$configValue || $forceSyncMode) {
            $addressMock = $this->createMock(Address::class);

            $this->addressRenderer->expects($this->any())
                ->method('format')
                ->with($addressMock, 'html')
                ->willReturn($address);

            $this->orderMock->expects($this->any())
                ->method('getBillingAddress')
                ->willReturn($addressMock);

            $this->orderMock->expects($this->any())
                ->method('getShippingAddress')
                ->willReturn($addressMock);

            $this->orderMock->expects($this->any())
                ->method('getCustomerName')
                ->willReturn($customerName);

            $this->orderMock->expects($this->once())
                ->method('getIsNotVirtual')
                ->willReturn($isNotVirtual);

            $this->orderMock->expects($this->once())
                ->method('getEmailCustomerNote')
                ->willReturn('');

            $this->orderMock->expects($this->once())
                ->method('getFrontendStatusLabel')
                ->willReturn($frontendStatusLabel);

            $this->invoiceMock->expects($this->once())
                ->method('getCustomerNoteNotify')
                ->willReturn($customerNoteNotify);

            $this->invoiceMock->expects($this->any())
                ->method('getCustomerNote')
                ->willReturn($comment);

            $this->templateContainerMock->expects($this->once())
                ->method('setTemplateVars')
                ->with(
                    [
                        'order' => $this->orderMock,
                        'order_id' => self::ORDER_ID,
                        'invoice' => $this->invoiceMock,
                        'invoice_id' => self::INVOICE_ID,
                        'comment' => $customerNoteNotify ? $comment : '',
                        'billing' => $addressMock,
                        'payment_html' => 'payment',
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => $address,
                        'formattedBillingAddress' => $address,
                        'order_data' => [
                            'customer_name' => $customerName,
                            'is_not_virtual' => $isNotVirtual,
                            'email_customer_note' => '',
                            'frontend_status_label' => $frontendStatusLabel
                        ]
                    ]
                );
            $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
            $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
            $this->identityContainerMock->expects($this->exactly(2))
                ->method('isEnabled')
                ->willReturn($emailSendingResult);

            if ($emailSendingResult) {
                $this->identityContainerMock->expects($this->once())
                    ->method('getCopyMethod')
                    ->willReturn('copy');

                $this->senderBuilderFactoryMock->expects($this->once())
                    ->method('create')
                    ->willReturn($this->senderMock);

                $this->senderMock->expects($this->once())->method('send');

                $this->senderMock->expects($this->once())->method('sendCopyTo');

                $this->invoiceMock->expects($this->once())
                    ->method('setEmailSent')
                    ->with(true);

                $this->invoiceResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->invoiceMock, ['send_email', 'email_sent']);

                $this->assertTrue(
                    $this->sender->send($this->invoiceMock)
                );
            } else {
                $this->invoiceResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->invoiceMock, 'send_email');

                $this->assertFalse(
                    $this->sender->send($this->invoiceMock)
                );
            }
        } else {
            $this->invoiceResourceMock
                ->method('saveAttribute')
                ->withConsecutive(
                    [$this->invoiceMock, 'email_sent'],
                    [$this->invoiceMock, 'send_email']
                );

            $this->assertFalse(
                $this->sender->send($this->invoiceMock)
            );
        }
    }

    /**
     * @return array
     */
    public function sendDataProvider(): array
    {
        return [
            [0, 0, 1, true],
            [0, 0, 0, true],
            [0, 0, 1, false],
            [0, 0, 0, false],
            [0, 1, 1, true],
            [0, 1, 0, true],
            [1, null, null, null]
        ];
    }

    /**
     * @param bool $isVirtualOrder
     * @param int $formatCallCount
     * @param string|null $expectedShippingAddress
     *
     * @return void
     * @dataProvider sendVirtualOrderDataProvider
     */
    public function testSendVirtualOrder(
        bool $isVirtualOrder,
        int $formatCallCount,
        ?string $expectedShippingAddress
    ): void {
        $billingAddress = 'address_test';
        $this->orderMock->setData(OrderInterface::IS_VIRTUAL, $isVirtualOrder);
        $customerName = 'Test Customer';
        $frontendStatusLabel = 'Complete';
        $isNotVirtual = false;

        $this->invoiceMock->expects($this->once())
            ->method('setSendEmail')
            ->with(false);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with('sales_email/general/async_sending')
            ->willReturn(false);

        $addressMock = $this->createMock(Address::class);

        $this->addressRenderer->expects($this->exactly($formatCallCount))
            ->method('format')
            ->with($addressMock, 'html')
            ->willReturn($billingAddress);

        $this->stepAddressFormat($addressMock, $isVirtualOrder);

        $this->invoiceMock->expects($this->once())
            ->method('getCustomerNoteNotify')
            ->willReturn(false);
        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($customerName);

        $this->orderMock->expects($this->once())
            ->method('getIsNotVirtual')
            ->willReturn($isNotVirtual);

        $this->orderMock->expects($this->once())
            ->method('getEmailCustomerNote')
            ->willReturn('');

        $this->orderMock->expects($this->once())
            ->method('getFrontendStatusLabel')
            ->willReturn($frontendStatusLabel);

        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                [
                    'order' => $this->orderMock,
                    'order_id' => self::ORDER_ID,
                    'invoice' => $this->invoiceMock,
                    'invoice_id' => self::INVOICE_ID,
                    'comment' => '',
                    'billing' => $addressMock,
                    'payment_html' => 'payment',
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => $expectedShippingAddress,
                    'formattedBillingAddress' => $billingAddress,
                    'order_data' => [
                        'customer_name' => $customerName,
                        'is_not_virtual' => false,
                        'email_customer_note' => '',
                        'frontend_status_label' => $frontendStatusLabel
                    ]
                ]
            );
        $this->appEmulator->expects($this->once())->method('startEnvironmentEmulation');
        $this->appEmulator->expects($this->once())->method('stopEnvironmentEmulation');
        $this->identityContainerMock->expects($this->exactly(2))
            ->method('isEnabled')
            ->willReturn(false);

        $this->invoiceResourceMock->expects($this->once())
            ->method('saveAttribute')
            ->with($this->invoiceMock, 'send_email');

        $this->assertFalse($this->sender->send($this->invoiceMock));
    }

    /**
     * @return array
     */
    public function sendVirtualOrderDataProvider(): array
    {
        return [
            [true, 1, null],
            [false, 2, 'address_test']
        ];
    }
}
