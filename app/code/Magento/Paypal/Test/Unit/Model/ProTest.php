<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\Paypal\Model\Pro
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Model\Config as PaypalConfig;

class ProTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Pro
     */
    protected $_pro;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected  $_apiMock;

    protected function setUp()
    {
        $objectHelper = new ObjectManager($this);
        $infoFactory = $this->_getInfoFactory();

        $storeId = 33;
        $configFactory = $this->_getConfigFactory($storeId);
        $apiFactory = $this->_getApiFactory($objectHelper);
        $args = $objectHelper->getConstructArguments(
            'Magento\Paypal\Model\Pro',
            [
                'configFactory' => $configFactory,
                'infoFactory' => $infoFactory,
                'apiFactory' => $apiFactory
            ]
        );
        /** @var $pro \Magento\Paypal\Model\Pro */
        $this->_pro = $this->getMock('Magento\Paypal\Model\Pro', ['_isPaymentReviewRequired'], $args);
        $this->_pro->setMethod(PaypalConfig::METHOD_PAYMENT_PRO, $storeId);
    }

    /**
     * @param bool $pendingReason
     * @param bool $isReviewRequired
     * @param bool $expected
     * @dataProvider canReviewPaymentDataProvider
     */
    public function testCanReviewPayment($pendingReason, $isReviewRequired, $expected)
    {
        $this->_pro->expects(
            $this->any()
        )->method(
            '_isPaymentReviewRequired'
        )->will(
            $this->returnValue($isReviewRequired)
        );
        $payment = $this->getMockBuilder(
            'Magento\Payment\Model\Info'
        )->disableOriginalConstructor()->setMethods(
            ['getAdditionalInformation', '__wakeup']
        )->getMock();
        $payment->expects(
            $this->once()
        )->method(
            'getAdditionalInformation'
        )->with(
            $this->equalTo(\Magento\Paypal\Model\Info::PENDING_REASON_GLOBAL)
        )->will(
            $this->returnValue($pendingReason)
        );

        $this->assertEquals($expected, $this->_pro->canReviewPayment($payment));
    }

    /**
     * @return array
     */
    public function canReviewPaymentDataProvider()
    {
        return [
            [\Magento\Paypal\Model\Info::PAYMENTSTATUS_REVIEW, true, false],
            [\Magento\Paypal\Model\Info::PAYMENTSTATUS_REVIEW, false, false],
            ['another_pending_reason', false, false],
            ['another_pending_reason', true, true]
        ];
    }

    /**
     * @covers \Magento\Paypal\Model\Pro::capture
     */
    public function testCapture()
    {
        $paymentMock = $this->_getPaymentMock();
        $orderMock = $this->_getOrderMock();

        $this->_apiMock->expects(static::any())
            ->method('setAuthorizationId')
            ->willReturnSelf();
        $this->_apiMock->expects(static::any())
            ->method('setIsCaptureComplete')
            ->willReturnSelf();
        $this->_apiMock->expects(static::any())
            ->method('setAmount')
            ->willReturnSelf();

        $paymentMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->_apiMock->expects(static::once())
            ->method('getTransactionId')
            ->willReturn(45);
        $this->_apiMock->expects(static::any())
            ->method('getDataUsingMethod')
            ->willReturn(false);

        $amount = 43.03;
        $this->_pro->capture($paymentMock, $amount);
    }

    /**
     * Create and return mock of info factory
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getInfoFactory()
    {
        $infoFactory = $this->getMockBuilder('Magento\Paypal\Model\InfoFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $infoMock = $this->getMockBuilder('Magento\Paypal\Model\Info')
            ->disableOriginalConstructor()
            ->setMethods(['isPaymentReviewRequired'])
            ->getMock();
        $infoFactory->expects(static::any())->method('create')->willReturn($infoMock);
        return $infoFactory;
    }

    /**
     * Create and return mock of config factory
     * @param $storeId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getConfigFactory($storeId)
    {
        $configType = 'Magento\Paypal\Model\Config';
        $configMock = $this->getMockBuilder($configType)
            ->disableOriginalConstructor()
            ->getMock();
        $configFactory = $this->getMockBuilder('Magento\Paypal\Model\Config\Factory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $configFactory->expects(static::any())
            ->method('create')
            ->with($configType, ['params' => [
                PaypalConfig::METHOD_PAYMENT_PRO,
                $storeId
            ]])
            ->willReturn($configMock);
        return $configFactory;
    }

    /**
     * Create mock object for paypal api factory
     * @param ObjectManager $objectHelper
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getApiFactory(ObjectManager $objectHelper)
    {
        $apiFactory = $this->getMockBuilder('Magento\Paypal\Model\Api\Type\Factory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $httpClient = $this->getMockBuilder('Magento\Framework\HTTP\Adapter\Curl')
            ->disableOriginalConstructor()
            ->getMock();

        $httpClient->expects(static::any())
            ->method('read')
            ->will(static::returnValue(
                "\r\n" . 'ACK=Success&CORRELATIONID=32342431'
            ));

        $curlFactory = $this->getMockBuilder('Magento\Framework\HTTP\Adapter\CurlFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $curlFactory->expects(static::any())->method('create')->willReturn($httpClient);

        $apiType = 'Magento\Paypal\Model\Api\Nvp';
        $args = $objectHelper->getConstructArguments(
            $apiType,
            [
                'curlFactory' => $curlFactory
            ]
        );
        $this->_apiMock = $this->getMockBuilder($apiType)
            ->setConstructorArgs($args)
            ->setMethods(['__wakeup', 'getTransactionId', 'getDataUsingMethod'])
            ->getMock();

        $apiFactory->expects(static::any())->method('create')->with($apiType)->willReturn($this->_apiMock);
        return $apiFactory;
    }

    /**
     * Create mock object for payment model
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getPaymentMock()
    {
        $paymentMock = $this->getMockBuilder('Magento\Payment\Model\Info')
            ->disableOriginalConstructor()
            ->setMethods([
                'getParentTransactionId', 'getOrder', 'getShouldCloseParentTransaction'
            ])
            ->getMock();
        $parentTransactionId = 43;
        $paymentMock->expects(static::once())
            ->method('getParentTransactionId')
            ->willReturn($parentTransactionId);
        $paymentMock->expects(static::once())
            ->method('getShouldCloseParentTransaction')
            ->willReturn(true);
        return $paymentMock;
    }

    /**
     * Create mock object for order model
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock()
    {
        $orderData = [
            'currency' => 'USD',
            'id' => 4,
            'increment_id' => '0000004'
        ];
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrencyCode', 'getIncrementId', 'getId', 'getBillingAddress', 'getShippingAddress'])
            ->getMock();

        $orderMock->expects(static::once())
            ->method('getId')
            ->willReturn($orderData['id']);
        $orderMock->expects(static::once())
            ->method('getBaseCurrencyCode')
            ->willReturn($orderData['currency']);
        $orderMock->expects(static::atLeastOnce())
            ->method('getIncrementId')
            ->willReturn($orderData['increment_id']);
        return $orderMock;
    }
}
