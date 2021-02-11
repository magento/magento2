<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Request\DescriptorDataBuilder;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Test for DescriptorDataBuilder
 */
class DescriptorDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var DescriptorDataBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDynamicDescriptors'])
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new DescriptorDataBuilder($this->configMock, $this->subjectReaderMock);
    }

    /**
     * @param array $descriptors
     * @param array $expected
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $descriptors, array $expected)
    {
        $paymentDOMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $buildSubject = [
            'payment' => $paymentDOMock,
        ];
        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($paymentDOMock);

        $order = $this->getMockForAbstractClass(OrderAdapterInterface::class);
        $order->expects(self::once())->method('getStoreId')->willReturn('1');

        $paymentDOMock->expects(self::once())->method('getOrder')->willReturn($order);

        $this->configMock->method('getDynamicDescriptors')->willReturn($descriptors);

        $actual = $this->builder->build(['payment' => $paymentDOMock]);
        static::assertEquals($expected, $actual);
    }

    /**
     * Get variations for build method testing
     * @return array
     */
    public function buildDataProvider()
    {
        $name = 'company * product';
        $phone = '333-22-22-333';
        $url = 'https://test.url.mage.com';
        return [
            [
                'descriptors' => [
                    'name' => $name,
                    'phone' => $phone,
                    'url' => $url,
                ],
                'expected' => [
                    'descriptor' => [
                        'name' => $name,
                        'phone' => $phone,
                        'url' => $url,
                    ],
                ],
            ],
            [
                'descriptors' => [
                    'name' => $name,
                    'phone' => $phone,
                ],
                'expected' => [
                    'descriptor' => [
                        'name' => $name,
                        'phone' => $phone,
                    ],
                ],
            ],
            [
                'descriptors' => [
                    'name' => $name,
                ],
                'expected' => [
                    'descriptor' => [
                        'name' => $name,
                    ],
                ],
            ],
            [
                'descriptors' => [],
                'expected' => [],
            ],
        ];
    }
}
