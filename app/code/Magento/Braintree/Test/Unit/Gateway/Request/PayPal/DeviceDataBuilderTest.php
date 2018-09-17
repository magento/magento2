<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request\PayPal;

use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Request\PayPal\DeviceDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class DeviceDataBuilderTest
 */
class DeviceDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

    /**
     * @var InfoInterface|MockObject
     */
    private $paymentInfo;

    /**
     * @var DeviceDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->paymentInfo = $this->getMockForAbstractClass(InfoInterface::class);
        
        $this->builder = new DeviceDataBuilder(new SubjectReader());
    }

    /**
     * @covers \Magento\Braintree\Gateway\Request\PayPal\DeviceDataBuilder::build
     * @param array $paymentData
     * @param array $expected
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $paymentData, array $expected)
    {
        $subject = [
            'payment' => $this->paymentDO
        ];

        $this->paymentDO->method('getPayment')
            ->willReturn($this->paymentInfo);

        $this->paymentInfo->method('getAdditionalInformation')
            ->willReturn($paymentData);

        $actual = $this->builder->build($subject);
        self::assertEquals($expected, $actual);
    }

    /**
     * Get variations for build method testing
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            [
                'paymentData' => [
                    'device_data' => '{correlation_id: 12s3jf9as}'
                ],
                'expected' => [
                    'deviceData' => '{correlation_id: 12s3jf9as}'
                ]
            ],
            [
                'paymentData' => [
                    'device_data' => null,
                ],
                'expected' => []
            ],
            [
                'paymentData' => [
                    'deviceData' => '{correlation_id: 12s3jf9as}',
                ],
                'expected' => []
            ],
            [
                'paymentData' => [],
                'expected' => []
            ]
        ];
    }
}
