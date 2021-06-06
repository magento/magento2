<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Magento\Sales\Model\ResourceModel\Order;
use PHPUnit\Framework\MockObject\MockObject;

class OrderSenderTest extends AbstractSenderTest
{
    private const ORDER_ID = 1;

    /**
     * @var OrderSender
     */
    protected $sender;

    /**
     * @var EntityAbstract|MockObject
     */
    protected $orderResourceMock;

    protected function setUp(): void
    {
        $this->stepMockSetup();

        $this->orderResourceMock = $this->createPartialMock(
            Order::class,
            ['saveAttribute']
        );

        $this->identityContainerMock = $this->createPartialMock(
            OrderIdentity::class,
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId', 'getCopyMethod']
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->orderMock->method('getId')
            ->willReturn(self::ORDER_ID);

        $this->sender = new OrderSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRenderer,
            $this->paymentHelper,
            $this->orderResourceMock,
            $this->globalConfig,
            $this->eventManagerMock
        );
    }

    /**
     * @param int $configValue
     * @param bool|null $forceSyncMode
     * @param bool|null $emailSendingResult
     * @param $senderSendException
     * @return void
     * @dataProvider sendDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSend($configValue, $forceSyncMode, $emailSendingResult, $senderSendException)
    {
        $address = 'address_test';
        $configPath = 'sales_email/general/async_sending';
        $createdAtFormatted='Oct 14, 2019, 4:11:58 PM';
        $customerName = 'test customer';
        $frontendStatusLabel = 'Processing';
        $isNotVirtual = true;

        $this->orderMock->expects($this->once())
            ->method('setSendEmail')
            ->with($emailSendingResult);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with($configPath)
            ->willReturn($configValue);

        if (!$configValue || $forceSyncMode) {
            $this->identityContainerMock->expects($this->exactly(2))
                ->method('isEnabled')
                ->willReturn($emailSendingResult);

            if ($emailSendingResult) {
                $this->identityContainerMock->expects($senderSendException ? $this->never() : $this->once())
                    ->method('getCopyMethod')
                    ->willReturn('copy');

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

                $this->orderMock->expects($this->once())
                    ->method('getCreatedAtFormatted')
                    ->with(2)
                    ->willReturn($createdAtFormatted);

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
                            'billing' => $addressMock,
                            'payment_html' => 'payment',
                            'store' => $this->storeMock,
                            'formattedShippingAddress' => $address,
                            'formattedBillingAddress' => $address,
                            'created_at_formatted'=>$createdAtFormatted,
                            'order_data' => [
                                'customer_name' => $customerName,
                                'is_not_virtual' => $isNotVirtual,
                                'email_customer_note' => '',
                                'frontend_status_label' => $frontendStatusLabel
                            ]

                        ]
                    );

                $this->senderBuilderFactoryMock->expects($this->once())
                    ->method('create')
                    ->willReturn($this->senderMock);

                $this->senderMock->expects($this->once())->method('send');

                if ($senderSendException) {
                    $this->checkSenderSendExceptionCase();
                } else {
                    $this->senderMock->expects($this->once())->method('sendCopyTo');

                    $this->orderMock->expects($this->once())
                        ->method('setEmailSent')
                        ->with($emailSendingResult);

                    $this->orderResourceMock->expects($this->once())
                        ->method('saveAttribute')
                        ->with($this->orderMock, ['send_email', 'email_sent']);

                    $this->assertTrue(
                        $this->sender->send($this->orderMock)
                    );
                }
            } else {
                $this->orderResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->orderMock, 'send_email');

                $this->assertFalse(
                    $this->sender->send($this->orderMock)
                );
            }
        } else {
            $this->orderResourceMock->expects($this->at(0))
                ->method('saveAttribute')
                ->with($this->orderMock, 'email_sent');
            $this->orderResourceMock->expects($this->at(1))
                ->method('saveAttribute')
                ->with($this->orderMock, 'send_email');

            $this->assertFalse(
                $this->sender->send($this->orderMock)
            );
        }
    }

    /**
     * Methods check case when method "send" in "senderMock" throw exception.
     *
     * @return void
     */
    protected function checkSenderSendExceptionCase()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception('exception'));

        $this->orderResourceMock->expects($this->once())
            ->method('saveAttribute')
            ->with($this->orderMock, 'send_email');

        $this->assertFalse(
            $this->sender->send($this->orderMock)
        );
    }

    /**
     * @return array
     */
    public function sendDataProvider()
    {
        return [
            [0, 0, true, false],
            [0, 0, true, false],
            [0, 0, true, true],
            [0, 0, false, false],
            [0, 0, false, false],
            [0, 0, false, true],
            [0, 1, true, false],
            [0, 1, true, false],
            [0, 1, true, false],
            [1, null, null, false]
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
        $address = 'address_test';
        $this->orderMock->setData(OrderInterface::IS_VIRTUAL, $isVirtualOrder);
        $createdAtFormatted='Oct 14, 2019, 4:11:58 PM';
        $customerName = 'test customer';
        $frontendStatusLabel = 'Complete';
        $isNotVirtual = false;

        $this->orderMock->expects($this->once())
            ->method('setSendEmail')
            ->with(true);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with('sales_email/general/async_sending')
            ->willReturn(false);

        $this->identityContainerMock->expects($this->exactly(2))
            ->method('isEnabled')
            ->willReturn(true);

        $this->identityContainerMock->expects($this->once())
            ->method('getCopyMethod')
            ->willReturn('copy');

        $addressMock = $this->createMock(Address::class);

        $this->addressRenderer->expects($this->exactly($formatCallCount))
            ->method('format')
            ->with($addressMock, 'html')
            ->willReturn($address);

        $this->stepAddressFormat($addressMock, $isVirtualOrder);

        $this->orderMock->expects($this->once())
            ->method('getCreatedAtFormatted')
            ->with(2)
            ->willReturn($createdAtFormatted);

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
                    'billing' => $addressMock,
                    'payment_html' => 'payment',
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => $expectedShippingAddress,
                    'formattedBillingAddress' => $address,
                    'created_at_formatted'=>$createdAtFormatted,
                    'order_data' => [
                        'customer_name' => $customerName,
                        'is_not_virtual' => $isNotVirtual,
                        'email_customer_note' => '',
                        'frontend_status_label' => $frontendStatusLabel
                    ]
                ]
            );

        $this->senderBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->senderMock);

        $this->senderMock->expects($this->once())->method('send');

        $this->senderMock->expects($this->once())->method('sendCopyTo');

        $this->orderMock->expects($this->once())
            ->method('setEmailSent')
            ->with(true);

        $this->orderResourceMock->expects($this->once())
            ->method('saveAttribute')
            ->with($this->orderMock, ['send_email', 'email_sent']);

        $this->assertTrue($this->sender->send($this->orderMock));
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
