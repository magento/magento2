<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class CreditmemoSenderTest extends AbstractSenderTest
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
     */
    protected $sender;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\EntityAbstract|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoResourceMock;

    protected function setUp()
    {
        $this->stepMockSetup();

        $this->creditmemoResourceMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo::class,
            ['saveAttribute'],
            [],
            '',
            false
        );

        $this->creditmemoMock = $this->getMock(
            \Magento\Sales\Model\Order\Creditmemo::class,
            [
                'getStore', '__wakeup', 'getOrder',
                'setSendEmail', 'setEmailSent', 'getCustomerNoteNotify',
                'getCustomerNote'
            ],
            [],
            '',
            false
        );
        $this->creditmemoMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->creditmemoMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($this->orderMock));

        $this->identityContainerMock = $this->getMock(
            \Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity::class,
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId'],
            [],
            '',
            false
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

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

        $this->creditmemoMock->expects($this->once())
            ->method('setSendEmail')
            ->with(true);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with($configPath)
            ->willReturn($configValue);

        if (!$configValue || $forceSyncMode) {
            $addressMock = $this->getMock(
                \Magento\Sales\Model\Order\Address::class,
                [],
                [],
                '',
                false
            );

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

            $this->templateContainerMock->expects($this->once())
                ->method('setTemplateVars')
                ->with(
                    [
                        'order' => $this->orderMock,
                        'creditmemo' => $this->creditmemoMock,
                        'comment' => $customerNoteNotify ? $comment : '',
                        'billing' => $addressMock,
                        'payment_html' => 'payment',
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => $address,
                        'formattedBillingAddress' => $address
                    ]
                );

            $this->identityContainerMock->expects($this->once())
                ->method('isEnabled')
                ->willReturn($emailSendingResult);

            if ($emailSendingResult) {
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
     * @dataProvider sendVirtualOrderDataProvider
     */
    public function testSendVirtualOrder($isVirtualOrder, $formatCallCount, $expectedShippingAddress)
    {
        $billingAddress = 'address_test';

        $this->orderMock->setData(\Magento\Sales\Api\Data\OrderInterface::IS_VIRTUAL, $isVirtualOrder);

        $this->creditmemoMock->expects($this->once())
            ->method('setSendEmail')
            ->with(true);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with('sales_email/general/async_sending')
            ->willReturn(false);

        $addressMock = $this->getMock(\Magento\Sales\Model\Order\Address::class, [], [], '', false);

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
                    'creditmemo' => $this->creditmemoMock,
                    'comment' => '',
                    'billing' => $addressMock,
                    'payment_html' => 'payment',
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => $expectedShippingAddress,
                    'formattedBillingAddress' => $billingAddress
                ]
            );

        $this->identityContainerMock->expects($this->once())
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
