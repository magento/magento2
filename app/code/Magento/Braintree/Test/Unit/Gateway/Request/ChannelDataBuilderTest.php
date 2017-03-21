<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\ChannelDataBuilder;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class PaymentDataBuilderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChannelDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMetadataMock;

    /**
     * @var ChannelDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->productMetadataMock = $this->getMock(ProductMetadataInterface::class);
        $this->builder = new ChannelDataBuilder($this->productMetadataMock);
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
        $this->productMetadataMock->expects(static::once())
            ->method('getEdition')
            ->willReturn($edition);

        $this->assertEquals($expected, $this->builder->build($buildSubject));
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
