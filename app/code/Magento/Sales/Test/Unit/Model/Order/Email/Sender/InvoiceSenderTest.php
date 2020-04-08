<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

/**
 * Test for Magento\Sales\Model\Order\Email\Sender\InvoiceSender class.
 */
class InvoiceSenderTest extends AbstractSenderTest
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $sender;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\EntityAbstract|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceResourceMock;

    protected function setUp()
    {
        $this->stepMockSetup();

        $this->invoiceResourceMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Invoice::class,
            ['saveAttribute']
        );

        $this->invoiceMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Invoice::class,
            [
                'getStore',
                '__wakeup',
                'getOrder',
                'setSendEmail',
                'setEmailSent',
                'getCustomerNoteNotify',
                'getCustomerNote'
            ]
        );
        $this->invoiceMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->invoiceMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($this->orderMock));

        $this->identityContainerMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity::class,
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId', 'getCopyMethod']
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->sender = new InvoiceSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRenderer,
            $this->paymentHelper,
            $this->invoiceResourceMock,
            $this->globalConfig,
            $this->eventManagerMock
        );
    }

    /**
     * @param int $configValue
     * @param bool|null $forceSyncMode
     * @param bool|null $customerNoteNotify
     * @param bool|null $emailSendingResult
     * @dataProvider sendDataProvider
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSend($configValue, $forceSyncMode, $customerNoteNotify, $emailSendingResult)
    {
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
            $addressMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);

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
                        'invoice' => $this->invoiceMock,
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
            $this->invoiceResourceMock->expects($this->at(0))
                ->method('saveAttribute')
                ->with($this->invoiceMock, 'email_sent');
            $this->invoiceResourceMock->expects($this->at(1))
                ->method('saveAttribute')
                ->with($this->invoiceMock, 'send_email');

            $this->assertFalse(
                $this->sender->send($this->invoiceMock)
            );
        }
    }

    /**
     * @return array
     */
    public function sendDataProvider()
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
     * @dataProvider sendVirtualOrderDataProvider
     */
    public function testSendVirtualOrder($isVirtualOrder, $formatCallCount, $expectedShippingAddress)
    {
        $billingAddress = 'address_test';
        $this->orderMock->setData(\Magento\Sales\Api\Data\OrderInterface::IS_VIRTUAL, $isVirtualOrder);
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

        $addressMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);

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
                    'invoice' => $this->invoiceMock,
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
    public function sendVirtualOrderDataProvider()
    {
        return [
            [true, 1, null],
            [false, 2, 'address_test']
        ];
    }
}
