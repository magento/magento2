<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Paypal\Model\Pro
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Info;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Paypal\Model\Config\Factory;
use Magento\Paypal\Model\InfoFactory;
use Magento\Paypal\Model\Pro;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProTest extends TestCase
{
    /**
     * @var Pro
     */
    protected $pro;

    /** @var MockObject */
    protected $apiMock;

    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);
        $infoFactory = $this->getInfoFactory();

        $storeId = 33;
        $configFactory = $this->getConfigFactory($storeId);
        $apiFactory = $this->getApiFactory($objectHelper);
        $args = $objectHelper->getConstructArguments(
            Pro::class,
            [
                'configFactory' => $configFactory,
                'infoFactory' => $infoFactory,
                'apiFactory' => $apiFactory
            ]
        );
        /** @var \Magento\Paypal\Model\Pro $pro */
        $this->pro = $this->getMockBuilder(Pro::class)
            ->onlyMethods(['_isPaymentReviewRequired'])
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
        )->willReturn(
            $isReviewRequired
        );
        $payment = $this->getMockBuilder(
            Info::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['getAdditionalInformation', '__wakeup']
            )->getMock();
        $payment->expects(
            $this->once()
        )->method(
            'getAdditionalInformation'
        )->with(
            \Magento\Paypal\Model\Info::PENDING_REASON_GLOBAL
        )->willReturn(
            $pendingReason
        );

        $this->assertEquals($expected, $this->pro->canReviewPayment($payment));
    }

    /**
     * @return array
     */
    public static function canReviewPaymentDataProvider()
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
     * @return MockObject
     */
    protected function getInfoFactory()
    {
        $infoFactory = $this->getMockBuilder(InfoFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $infoMock = $this->getMockBuilder(\Magento\Paypal\Model\Info::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isPaymentReviewRequired'])
            ->getMock();
        $infoFactory->expects(static::any())->method('create')->willReturn($infoMock);
        return $infoFactory;
    }

    /**
     * Create and return mock of config factory
     * @param $storeId
     * @return MockObject
     */
    protected function getConfigFactory($storeId)
    {
        $configType = \Magento\Paypal\Model\Config::class;
        $configMock = $this->getMockBuilder($configType)
            ->disableOriginalConstructor()
            ->getMock();
        $configFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
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
     * @return MockObject
     */
    protected function getApiFactory(ObjectManager $objectHelper)
    {
        $apiFactory = $this->getMockBuilder(\Magento\Paypal\Model\Api\Type\Factory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $httpClient = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpClient->expects(static::any())
            ->method('read')
            ->willReturn(
                "\r\n" . 'ACK=Success&CORRELATIONID=32342431'
            );

        $curlFactory = $this->getMockBuilder(CurlFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $curlFactory->expects(static::any())->method('create')->willReturn($httpClient);

        $apiType = Nvp::class;
        $args = $objectHelper->getConstructArguments(
            $apiType,
            [
                'curlFactory' => $curlFactory
            ]
        );
        $this->apiMock = $this->getMockBuilder($apiType)
            ->setConstructorArgs($args)
            ->addMethods(['__wakeup', 'getTransactionId', 'setAuthorizationId', 'setIsCaptureComplete', 'setAmount'])
            ->onlyMethods(
                [
                    'getDataUsingMethod',
                ]
            )
            ->getMock();

        $apiFactory->expects(static::any())->method('create')->with($apiType)->willReturn($this->apiMock);
        return $apiFactory;
    }

    /**
     * Create mock object for payment model
     * @return MockObject
     */
    protected function getPaymentMock()
    {
        $paymentMock = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getParentTransactionId', 'getOrder', 'getShouldCloseParentTransaction', 'isCaptureFinal',
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
     * @return MockObject
     */
    protected function getOrderMock()
    {
        $orderData = [
            'currency' => 'USD',
            'id' => 4,
            'increment_id' => '0000004'
        ];
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBaseCurrencyCode', 'getIncrementId', 'getId', 'getBillingAddress', 'getShippingAddress'])
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
