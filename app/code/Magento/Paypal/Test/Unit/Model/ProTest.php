<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\Paypal\Model\Pro
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Model\Config as PaypalConfig;

/**
 * Class ProTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Paypal\Model\Pro
     */
    protected $pro;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected  $apiMock;

    protected function setUp()
    {
        $objectHelper = new ObjectManager($this);
        $infoFactory = $this->getInfoFactory();

        $storeId = 33;
        $configFactory = $this->getConfigFactory($storeId);
        $apiFactory = $this->getApiFactory($objectHelper);
        $args = $objectHelper->getConstructArguments(
            \Magento\Paypal\Model\Pro::class,
            [
                'configFactory' => $configFactory,
                'infoFactory' => $infoFactory,
                'apiFactory' => $apiFactory
            ]
        );
        /** @var $pro \Magento\Paypal\Model\Pro */
        $this->pro = $this->getMockBuilder(\Magento\Paypal\Model\Pro::class)
            ->setMethods(['_isPaymentReviewRequired'])
            ->setConstructorArgs($args)
            ->getMock();
        $this->pro->setMethod(PaypalConfig::METHOD_PAYMENT_PRO, $storeId);
    }

    /**
     * @param bool $pendingReason
     * @param bool $isReviewRequired
     * @param bool $expected
     * @dataProvider canReviewPaymentDataProvider
     */
    public function testCanReviewPayment($pendingReason, $isReviewRequired, $expected)
    {
        $this->pro->expects(
            $this->any()
        )->method(
            '_isPaymentReviewRequired'
        )->will(
            $this->returnValue($isReviewRequired)
        );
        $payment = $this->getMockBuilder(
            \Magento\Payment\Model\Info::class
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

        $this->assertEquals($expected, $this->pro->canReviewPayment($payment));
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
        $paymentMock = $this->getPaymentMock();
        $orderMock = $this->getOrderMock();

        $this->apiMock->expects(static::any())
            ->method('setAuthorizationId')
            ->willReturnSelf();
        $this->apiMock->expects(static::any())
            ->method('setIsCaptureComplete')
            ->willReturnSelf();
        $this->apiMock->expects(static::any())
            ->method('setAmount')
            ->willReturnSelf();

        $paymentMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $paymentMock->method('isCaptureFinal')
            ->willReturn(true);

        $this->apiMock->expects(static::once())
            ->method('getTransactionId')
            ->willReturn(45);
        $this->apiMock->expects(static::any())
            ->method('getDataUsingMethod')
            ->willReturn(false);

        $amount = 43.03;
        $this->pro->capture($paymentMock, $amount);
    }

    /**
     * Create and return mock of info factory
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getInfoFactory()
    {
        $infoFactory = $this->getMockBuilder(\Magento\Paypal\Model\InfoFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $infoMock = $this->getMockBuilder(\Magento\Paypal\Model\Info::class)
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
    protected function getConfigFactory($storeId)
    {
        $configType = \Magento\Paypal\Model\Config::class;
        $configMock = $this->getMockBuilder($configType)
            ->disableOriginalConstructor()
            ->getMock();
        $configFactory = $this->getMockBuilder(\Magento\Paypal\Model\Config\Factory::class)
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
    protected function getApiFactory(ObjectManager $objectHelper)
    {
        $apiFactory = $this->getMockBuilder(\Magento\Paypal\Model\Api\Type\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $httpClient = $this->getMockBuilder(\Magento\Framework\HTTP\Adapter\Curl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpClient->expects(static::any())
            ->method('read')
            ->will(static::returnValue(
                "\r\n" . 'ACK=Success&CORRELATIONID=32342431'
            ));

        $curlFactory = $this->getMockBuilder(\Magento\Framework\HTTP\Adapter\CurlFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $curlFactory->expects(static::any())->method('create')->willReturn($httpClient);

        $apiType = \Magento\Paypal\Model\Api\Nvp::class;
        $args = $objectHelper->getConstructArguments(
            $apiType,
            [
                'curlFactory' => $curlFactory
            ]
        );
        $this->apiMock = $this->getMockBuilder($apiType)
            ->setConstructorArgs($args)
            ->setMethods(['__wakeup', 'getTransactionId', 'getDataUsingMethod', 'setAuthorizationId', 'setIsCaptureComplete', 'setAmount', ])
            ->getMock();

        $apiFactory->expects(static::any())->method('create')->with($apiType)->willReturn($this->apiMock);
        return $apiFactory;
    }

    /**
     * Create mock object for payment model
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPaymentMock()
    {
        $paymentMock = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getParentTransactionId', 'getOrder', 'getShouldCloseParentTransaction', 'isCaptureFinal'
            ])
            ->getMock();
        $parentTransactionId = 43;
        $paymentMock->expects(static::once())
            ->method('getParentTransactionId')
            ->willReturn($parentTransactionId);

        return $paymentMock;
    }

    /**
     * Create mock object for order model
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock()
    {
        $orderData = [
            'currency' => 'USD',
            'id' => 4,
            'increment_id' => '0000004'
        ];
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
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
