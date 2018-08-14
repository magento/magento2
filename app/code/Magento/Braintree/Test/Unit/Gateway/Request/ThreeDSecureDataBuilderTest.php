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
 * Class ThreeDSecureDataBuilderTest
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
    private $paymentDO;

    /**
     * @var OrderAdapter|MockObject
     */
    private $order;

    /**
     * @var AddressAdapter|MockObject
     */
    private $billingAddress;

    protected function setUp()
    {
        $this->initOrderMock();

        $this->paymentDO = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

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
            'payment' => $this->paymentDO,
            'amount' => 25
        ];

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
     */
    private function initOrderMock()
    {
        $this->billingAddress = $this->getMockBuilder(AddressAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->order = $this->getMockBuilder(OrderAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->order->method('getBillingAddress')
            ->willReturn($this->billingAddress);
        $this->order->method('getStoreId')
            ->willReturn(self::$storeId);
    }
}
