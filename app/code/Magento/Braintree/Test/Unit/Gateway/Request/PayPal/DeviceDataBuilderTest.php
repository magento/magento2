<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request\PayPal;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\PayPal\DeviceDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Request\PayPal\DeviceDataBuilder.
 */
class DeviceDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var InfoInterface|MockObject
     */
    private $paymentInfoMock;

    /**
     * @var DeviceDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readPayment'])
            ->getMock();

        $this->paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);

        $this->paymentInfoMock = $this->createMock(InfoInterface::class);
        
        $this->builder = new DeviceDataBuilder($this->subjectReaderMock);
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
            'payment' => $this->paymentDataObjectMock,
        ];

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($this->paymentDataObjectMock);

        $this->paymentDataObjectMock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentInfoMock);

        $this->paymentInfoMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn($paymentData);

        $actual = $this->builder->build($subject);
        static::assertEquals($expected, $actual);
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
