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
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Request\ThreeDSecureDataBuilder.
 */
class ThreeDSecureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ThreeDSecureDataBuilder
     */
    private $builder;

    /**
     * @var Config|MockObject
     */
    private $configMock;

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

    /**
     * @var SubjectReader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectReaderMock;

    /**
     * @var int
     */
    private $storeId = 1;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->initOrderMock();

        $this->paymentDOMock = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'getPayment'])
            ->getMockForAbstractClass();
        $this->paymentDOMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isVerify3DSecure', 'getThresholdAmount', 'get3DSecureSpecificCountries'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new ThreeDSecureDataBuilder($this->configMock, $this->subjectReaderMock);
    }

    /**
     * @param bool $verify
     * @param float $thresholdAmount
     * @param string $countryId
     * @param array $countries
     * @param array $expected
     * @covers \Magento\Braintree\Gateway\Request\ThreeDSecureDataBuilder::build
     * @dataProvider buildDataProvider
     */
    public function testBuild($verify, $thresholdAmount, $countryId, array $countries, array $expected)
    {
        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => 25,
        ];

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
     *
     * @return void
     */
    private function initOrderMock()
    {
        $this->billingAddressMock = $this->getMockBuilder(AddressAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountryId'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(OrderAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAddress', 'getStoreId'])
            ->getMock();

        $this->orderMock->expects(static::any())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressMock);
        $this->orderMock->method('getStoreId')
            ->willReturn($this->storeId);
    }
}
