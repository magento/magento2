<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\ThreeDSecureDataBuilder;
use Magento\Payment\Gateway\Data\Order\AddressAdapter;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Request\ThreeDSecureDataBuilder.
 */
class ThreeDSecureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    private static $storeId = 1;

    /**
     * @var ThreeDSecureDataBuilder
     */
    private $builder;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @var OrderAdapter|MockObject
     */
    private $orderMock;

    /**
     * @var AddressAdapter|MockObject
     */
    private $billingAddressMock;

<<<<<<< HEAD
    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    /**
     * @var int
     */
    private $storeId = 1;

    /**
     * @inheritdoc
     */
=======
>>>>>>> upstream/2.2-develop
    protected function setUp()
    {
        $this->initOrderMock();

        $this->paymentDOMock = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
<<<<<<< HEAD
        $this->paymentDOMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
=======
        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);
>>>>>>> upstream/2.2-develop

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new ThreeDSecureDataBuilder($this->config, new SubjectReader());
    }

    /**
     * @param bool $verify
     * @param float $thresholdAmount
     * @param string $countryId
     * @param array $countries
     * @param array $expected
     * @dataProvider buildDataProvider
     */
    public function testBuild($verify, $thresholdAmount, $countryId, array $countries, array $expected)
    {
        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => 25,
        ];

<<<<<<< HEAD
        $this->configMock->expects(static::once())
            ->method('isVerify3DSecure')
            ->with(self::equalTo($this->storeId))
            ->willReturn($verify);

        $this->configMock->expects(static::any())
            ->method('getThresholdAmount')
            ->with(self::equalTo($this->storeId))
            ->willReturn($thresholdAmount);

        $this->configMock->expects(static::any())
            ->method('get3DSecureSpecificCountries')
            ->with(self::equalTo($this->storeId))
            ->willReturn($countries);

        $this->billingAddressMock->expects(static::any())
            ->method('getCountryId')
            ->willReturn($countryId);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn(25);

=======
        $this->config->method('isVerify3DSecure')
            ->with(self::equalTo(self::$storeId))
            ->willReturn($verify);

        $this->config->method('getThresholdAmount')
            ->with(self::equalTo(self::$storeId))
            ->willReturn($thresholdAmount);

        $this->config->method('get3DSecureSpecificCountries')
            ->with(self::equalTo(self::$storeId))
            ->willReturn($countries);

        $this->billingAddress->method('getCountryId')
            ->willReturn($countryId);

>>>>>>> upstream/2.2-develop
        $result = $this->builder->build($buildSubject);
        self::assertEquals($expected, $result);
    }

    /**
     * Gets list of variations to build request data.
     *
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            ['verify' => true, 'amount' => 20, 'countryId' => 'US', 'countries' => [], 'result' => [
                'options' => [
                    'three_d_secure' => [
                        'required' => true
                    ]
                ]
            ]],
            ['verify' => true, 'amount' => 0, 'countryId' => 'US', 'countries' => ['US', 'GB'], 'result' => [
                'options' => [
                    'three_d_secure' => [
                        'required' => true
                    ]
                ]
            ]],
            ['verify' => true, 'amount' => 40, 'countryId' => 'US', 'countries' => [], 'result' => []],
            ['verify' => false, 'amount' => 40, 'countryId' => 'US', 'countries' => [], 'result' => []],
            ['verify' => false, 'amount' => 20, 'countryId' => 'US', 'countries' => [], 'result' => []],
            ['verify' => true, 'amount' => 20, 'countryId' => 'CA', 'countries' => ['US', 'GB'], 'result' => []],
        ];
    }

    /**
     * Creates mock object for order adapter.
<<<<<<< HEAD
     *
     * @return void
=======
>>>>>>> upstream/2.2-develop
     */
    private function initOrderMock()
    {
        $this->billingAddressMock = $this->getMockBuilder(AddressAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(OrderAdapter::class)
            ->disableOriginalConstructor()
<<<<<<< HEAD
            ->setMethods(['getBillingAddress', 'getStoreId'])
            ->getMock();

        $this->orderMock->expects(static::any())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressMock);
        $this->orderMock->method('getStoreId')
            ->willReturn($this->storeId);
=======
            ->getMock();

        $this->order->method('getBillingAddress')
            ->willReturn($this->billingAddress);
        $this->order->method('getStoreId')
            ->willReturn(self::$storeId);
>>>>>>> upstream/2.2-develop
    }
}
