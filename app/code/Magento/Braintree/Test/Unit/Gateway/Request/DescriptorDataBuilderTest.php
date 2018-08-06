<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\DescriptorDataBuilder;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class DescriptorDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var DescriptorDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDynamicDescriptors'])
            ->getMock();

        $this->builder = new DescriptorDataBuilder($this->config, new SubjectReader());
    }

    /**
     * @param array $descriptors
     * @param array $expected
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $descriptors, array $expected)
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $order = $this->createMock(OrderAdapterInterface::class);
        $paymentDO->method('getOrder')
            ->willReturn($order);

        $this->config->method('getDynamicDescriptors')
            ->willReturn($descriptors);

        $actual = $this->builder->build(['payment' => $paymentDO]);
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
                    'url' => $url
                ],
                'expected' => [
                    'descriptor' => [
                        'name' => $name,
                        'phone' => $phone,
                        'url' => $url
                    ]
                ]
            ],
            [
                'descriptors' => [
                    'name' => $name,
                    'phone' => $phone
                ],
                'expected' => [
                    'descriptor' => [
                        'name' => $name,
                        'phone' => $phone
                    ]
                ]
            ],
            [
                'descriptors' => [
                    'name' => $name
                ],
                'expected' => [
                    'descriptor' => [
                        'name' => $name
                    ]
                ]
            ],
            [
                'descriptors' => [],
                'expected' => []
            ]
        ];
    }
}
