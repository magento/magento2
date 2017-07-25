<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\ChannelDataBuilder;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Payment\Gateway\Config\Config;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PaymentDataBuilderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChannelDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductMetadataInterface|MockObject
     */
    private $productMetadata;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var ChannelDataBuilder
     */
    private $builder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productMetadata = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->builder = new ChannelDataBuilder($this->productMetadata, $this->config);
    }

    /**
     * @param string $edition
     * @param array $expected
     * @covers \Magento\Braintree\Gateway\Request\ChannelDataBuilder::build
     * @dataProvider buildDataProvider
     */
    public function testBuild($edition, array $expected)
    {
        $buildSubject = [];

        $this->config->method('getValue')
            ->with(self::equalTo('channel'))
            ->willReturn(null);

        $this->productMetadata->method('getEdition')
            ->willReturn($edition);

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }

    /**
     * Checks a case when a channel provided via payment method configuration.
     */
    public function testBuildWithChannelFromConfig()
    {
        $channel = 'Magento2_Cart_ConfigEdition_BT';

        $this->config->method('getValue')
            ->with(self::equalTo('channel'))
            ->willReturn($channel);

        $this->productMetadata->expects(self::never())
            ->method('getEdition');

        self::assertEquals(
            [
                'channel' => $channel
            ],
            $this->builder->build([])
        );
    }

    /**
     * Get list of variations for build test
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            ['FirstEdition', ['channel' => 'Magento2_Cart_FirstEdition_BT']],
            ['SecondEdition', ['channel' => 'Magento2_Cart_SecondEdition_BT']],
        ];
    }
}
