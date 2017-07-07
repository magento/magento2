<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Last 4 digit of cc
     */
    const LAST4 = 1111;

    /**
     * Transaction ID
     */
    const TRID = '2217041665';

    /**
     * @var \Magento\Authorizenet\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->dataHelper = $helper->getObject(
            \Magento\Authorizenet\Helper\Data::class,
            ['storeManager' => $this->storeManagerMock]
        );
    }

    /**
     * @param $type
     * @param $amount
     * @param $exception
     * @param $additionalMessage
     * @param $expected
     * @dataProvider getMessagesParamDataProvider
     */
    public function testGetTransactionMessage($type, $amount, $exception, $additionalMessage, $expected)
    {
        $currency = $this->getMock(\Magento\Directory\Model\Currency::class, ['formatTxt', '__wakeup'], [], '', false);
        $currency->expects($this->any())
            ->method('formatTxt')
            ->will($this->returnValue($amount));
        $order = $this->getMock(\Magento\Sales\Model\Order::class, ['getBaseCurrency', '__wakeup'], [], '', false);
        $order->expects($this->any())
            ->method('getBaseCurrency')
            ->will($this->returnValue($currency));
        $payment = $this->getMock(\Magento\Payment\Model\Info::class, ['getOrder', '__wakeup'], [], '', false);
        $payment->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $card = new \Magento\Framework\DataObject(['cc_last_4' => self::LAST4]);
        $message = $this->dataHelper->getTransactionMessage(
            $payment,
            $type,
            self::TRID,
            $card,
            $amount,
            $exception,
            $additionalMessage
        );

        $this->assertEquals($expected, $message);
    }

    /**
     * @return array
     */
    public function getMessagesParamDataProvider()
    {
        $amount = 12.30;
        $additionalMessage = 'Addition message.';
        return [
            [
                'AUTH_ONLY',
                $amount,
                false,
                $additionalMessage,
                'Credit Card: xxxx-' . self::LAST4 . ' amount 12.3 authorize - successful. '
                . 'Authorize.Net Transaction ID ' . self::TRID . '. Addition message.',
            ],
            [
                'AUTH_CAPTURE',
                $amount,
                'some exception',
                false,
                'Credit Card: xxxx-' . self::LAST4 . ' amount 12.3 authorize and capture - failed. '
                . 'Authorize.Net Transaction ID ' . self::TRID . '. some exception'
            ],
            [
                'CREDIT',
                false,
                false,
                $additionalMessage,
                'Credit Card: xxxx-' . self::LAST4 . ' refund - successful. '
                . 'Authorize.Net Transaction ID ' . self::TRID . '. Addition message.'
            ],
        ];
    }

    public function testGetRelayUrl()
    {
        $storeId = 10;
        $baseUrl = 'http://base.url/';

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_LINK)
            ->willReturn($baseUrl);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);

        $this->assertSame(
            'http://base.url/authorizenet/directpost_payment/response',
            $this->dataHelper->getRelayUrl($storeId)
        );
    }

    /**
     * @param string $code
     * @param string $expected
     *
     * @dataProvider getFdsFilterActionLabelDataProvider
     */
    public function testGetFdsFilterActionLabel($code, $expected)
    {
        $this->assertSame($expected, (string)$this->dataHelper->getFdsFilterActionLabel($code));
    }

    /**
     * @return array
     */
    public function getFdsFilterActionLabelDataProvider()
    {
        return [
            ['decline ', 'Decline'],
            ['hold', 'Hold'],
            ['authAndHold', 'Authorize and Hold'],
            ['report', 'Report Only'],
            ['unknown_status', 'unknown_status']
        ];
    }

    /**
     * @param string $code
     * @param string $expected
     *
     * @dataProvider getTransactionStatusLabelDataProvider
     */
    public function testGetTransactionStatusLabel($code, $expected)
    {
        $this->assertSame($expected, (string)$this->dataHelper->getTransactionStatusLabel($code));
    }

    /**
     * @return array
     */
    public function getTransactionStatusLabelDataProvider()
    {
        return [
            ['authorizedPendingCapture', 'Authorized/Pending Capture'],
            ['capturedPendingSettlement', 'Captured/Pending Settlement'],
            ['refundSettledSuccessfully', 'Refund/Settled Successfully'],
            ['refundPendingSettlement', 'Refund/Pending Settlement'],
            ['declined', 'Declined'],
            ['expired', 'Expired'],
            ['voided', 'Voided'],
            ['FDSPendingReview', 'FDS - Pending Review'],
            ['FDSAuthorizedPendingReview', 'FDS - Authorized/Pending Review'],
            ['unknown_status', 'unknown_status']
        ];
    }
}
