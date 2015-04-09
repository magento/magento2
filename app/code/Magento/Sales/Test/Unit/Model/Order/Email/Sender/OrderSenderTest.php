<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use \Magento\Sales\Model\Order\Email\Sender\OrderSender;

class OrderSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $sender;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $senderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $senderBuilderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateContainerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $identityContainerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * Global configuration storage mock.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $globalConfig;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRenderer;

    protected function setUp()
    {
        $this->senderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender',
            ['send', 'sendCopyTo'],
            [],
            '',
            false
        );

        $this->senderBuilderFactoryMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\SenderBuilderFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->templateContainerMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Container\Template',
            ['setTemplateVars'],
            [],
            '',
            false
        );
        $this->paymentHelper = $this->getMock(
            '\Magento\Payment\Helper\Data',
            ['getInfoBlockHtml'],
            [],
            '',
            false
        );
        $this->paymentHelper->expects($this->any())
            ->method('getInfoBlockHtml')
            ->will($this->returnValue('payment'));

        $this->orderResource = $this->getMock(
            '\Magento\Sales\Model\Resource\Order',
            ['saveAttribute'],
            [],
            '',
            false
        );

        $this->globalConfig = $this->getMock(
            'Magento\Framework\App\Config',
            ['getValue'],
            [],
            '',
            false
        );

        $this->addressRenderer = $this->getMock(
            'Magento\Sales\Model\Order\Address\Renderer',
            ['format'],
            [],
            '',
            false
        );

        $this->storeMock = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getStoreId', '__wakeup'],
            [],
            '',
            false
        );

        $this->identityContainerMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Container\OrderIdentity',
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId'],
            [],
            '',
            false
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->orderMock = $this->getMock(
            '\Magento\Sales\Model\Order',
            [
                'getStore', 'getBillingAddress', 'getPayment',
                '__wakeup', 'getCustomerIsGuest', 'getCustomerName',
                'getCustomerEmail', 'setSendEmail', 'setEmailSent',
                'getShippingAddress'
            ],
            [],
            '',
            false
        );

        $this->orderMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $paymentInfoMock = $this->getMock(
            '\Magento\Payment\Model\Info',
            [],
            [],
            '',
            false
        );
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($paymentInfoMock));

        $this->sender = new OrderSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->paymentHelper,
            $this->orderResource,
            $this->globalConfig,
            $this->addressRenderer
        );
    }

    /**
     * @param int $configValue
     * @param bool|null $forceSyncMode
     * @param bool|null $emailSendingResult
     * @dataProvider sendDataProvider
     * @return void
     */
    public function testSend($configValue, $forceSyncMode, $emailSendingResult)
    {
        $address = 'address_test';
        $configPath = 'sales_email/general/async_sending';

        $this->orderMock->expects($this->once())
            ->method('setSendEmail')
            ->with(true);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with($configPath)
            ->willReturn($configValue);

        if (!$configValue || $forceSyncMode) {
            $this->identityContainerMock->expects($this->once())
                ->method('isEnabled')
                ->willReturn($emailSendingResult);

            if ($emailSendingResult) {
                $addressMock = $this->getMock(
                    'Magento\Sales\Model\Order\Address',
                    [],
                    [],
                    '',
                    false
                );

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

                $this->templateContainerMock->expects($this->once())
                    ->method('setTemplateVars')
                    ->with(
                        [
                            'order' => $this->orderMock,
                            'billing' => $addressMock,
                            'payment_html' => 'payment',
                            'store' => $this->storeMock,
                            'formattedShippingAddress' => $address,
                            'formattedBillingAddress' => $address
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

                $this->orderResource->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->orderMock, ['send_email', 'email_sent']);

                $this->assertTrue(
                    $this->sender->send($this->orderMock)
                );
            } else {
                $this->orderResource->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->orderMock, 'send_email');

                $this->assertFalse(
                    $this->sender->send($this->orderMock)
                );
            }
        } else {
            $this->orderResource->expects($this->once())
                ->method('saveAttribute')
                ->with($this->orderMock, 'send_email');

            $this->assertFalse(
                $this->sender->send($this->orderMock)
            );
        }
    }

    /**
     * @return array
     */
    public function sendDataProvider()
    {
        return [
            [0, 0, true],
            [0, 0, true],
            [0, 0, false],
            [0, 0, false],
            [0, 1, true],
            [0, 1, true],
            [1, null, null, null]
        ];
    }
}
