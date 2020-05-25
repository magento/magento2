<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as CreditmemoResource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for Magento\Sales\Model\Order\Email\Sender\CreditmemoSender class.
 */
class CreditmemoSenderTest extends AbstractSenderTest
{
    private const CREDITMEMO_ID = 1;

    private const ORDER_ID = 1;

    /**
     * @var CreditmemoSender
     */
    protected $sender;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditmemoMock;

    /**
     * @var EntityAbstract|MockObject
     */
    protected $creditmemoResourceMock;

    protected function setUp(): void
    {
        $this->stepMockSetup();

        $this->creditmemoResourceMock = $this->createPartialMock(
            CreditmemoResource::class,
            ['saveAttribute']
        );

        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->addMethods(['setSendEmail', 'getCustomerNoteNotify', 'getCustomerNote'])
            ->onlyMethods(['getStore', 'getId', 'getOrder', 'setEmailSent'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->creditmemoMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->method('getId')
            ->willReturn(self::CREDITMEMO_ID);
        $this->orderMock->method('getId')
            ->willReturn(self::ORDER_ID);

        $this->identityContainerMock = $this->createPartialMock(
            CreditmemoIdentity::class,
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId', 'getCopyMethod']
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->sender = new CreditmemoSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRenderer,
            $this->paymentHelper,
            $this->creditmemoResourceMock,
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
        $customerName = 'test customer';
        $frontendStatusLabel = 'Processing';
        $isNotVirtual = true;

        $this->creditmemoMock->expects($this->once())
            ->method('setSendEmail')
            ->with($emailSendingResult);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with($configPath)
            ->willReturn($configValue);

        if (!$configValue || $forceSyncMode) {
            $addressMock = $this->createMock(Address::class);

            $this->addressRenderer->expects($this->exactly(2))
                ->method('format')
                ->with($addressMock, 'html')
                ->willReturn($address);

            $this->stepAddressFormat($addressMock);

            $this->creditmemoMock->expects($this->once())
                ->method('getCustomerNoteNotify')
                ->willReturn($customerNoteNotify);

            $this->creditmemoMock->expects($this->any())
                ->method('getCustomerNote')
                ->willReturn($comment);

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
                        'creditmemo' => $this->creditmemoMock,
                        'creditmemo_id' => self::CREDITMEMO_ID,
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

                $this->creditmemoMock->expects($this->once())
                    ->method('setEmailSent')
                    ->with(true);

                $this->creditmemoResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->creditmemoMock, ['send_email', 'email_sent']);

                $this->assertTrue(
                    $this->sender->send($this->creditmemoMock)
                );
            } else {
                $this->creditmemoResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->creditmemoMock, 'send_email');

                $this->assertFalse(
                    $this->sender->send($this->creditmemoMock)
                );
            }
        } else {
            $this->creditmemoResourceMock->expects($this->at(0))
                ->method('saveAttribute')
                ->with($this->creditmemoMock, 'email_sent');
            $this->creditmemoResourceMock->expects($this->at(1))
                ->method('saveAttribute')
                ->with($this->creditmemoMock, 'send_email');

            $this->assertFalse(
                $this->sender->send($this->creditmemoMock)
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
     *
     * @return void
     * @dataProvider sendVirtualOrderDataProvider
     */
    public function testSendVirtualOrder($isVirtualOrder, $formatCallCount, $expectedShippingAddress)
    {
        $billingAddress = 'address_test';
        $customerName = 'test customer';
        $frontendStatusLabel = 'Complete';
        $isNotVirtual = false;

        $this->orderMock->setData(OrderInterface::IS_VIRTUAL, $isVirtualOrder);

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

        $this->creditmemoMock->expects($this->once())
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

        $this->creditmemoMock->expects($this->once())
            ->method('getCustomerNoteNotify')
            ->willReturn(true);

        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                [
                    'order' => $this->orderMock,
                    'order_id' => self::ORDER_ID,
                    'creditmemo' => $this->creditmemoMock,
                    'creditmemo_id' => self::CREDITMEMO_ID,
                    'comment' => '',
                    'billing' => $addressMock,
                    'payment_html' => 'payment',
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => $expectedShippingAddress,
                    'formattedBillingAddress' => $billingAddress,
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
            ->willReturn(false);

        $this->creditmemoResourceMock->expects($this->once())
            ->method('saveAttribute')
            ->with($this->creditmemoMock, 'send_email');

        $this->assertFalse($this->sender->send($this->creditmemoMock));
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
