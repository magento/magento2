<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\DescriptorDataBuilder;
<<<<<<< HEAD
=======
use Magento\Braintree\Gateway\SubjectReader;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDynamicDescriptors'])
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

<<<<<<< HEAD
        $this->builder = new DescriptorDataBuilder($this->config, new SubjectReader());
=======
        $this->builder = new DescriptorDataBuilder($this->configMock, $this->subjectReaderMock);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @param array $descriptors
     * @param array $expected
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $descriptors, array $expected)
    {
<<<<<<< HEAD
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $order = $this->createMock(OrderAdapterInterface::class);
        $paymentDO->method('getOrder')
            ->willReturn($order);

        $this->config->method('getDynamicDescriptors')
            ->willReturn($descriptors);

        $actual = $this->builder->build(['payment' => $paymentDO]);
=======
        $paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $buildSubject = [
            'payment' => $paymentDOMock,
        ];
        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($paymentDOMock);

        $order = $this->createMock(OrderAdapterInterface::class);
        $order->expects(self::once())->method('getStoreId')->willReturn('1');

        $paymentDOMock->expects(self::once())->method('getOrder')->willReturn($order);

        $this->configMock->method('getDynamicDescriptors')->willReturn($descriptors);

        $actual = $this->builder->build(['payment' => $paymentDOMock]);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
