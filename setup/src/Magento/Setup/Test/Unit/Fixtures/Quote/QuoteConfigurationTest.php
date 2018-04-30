<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures\Quote;

/**
 * Test for Magento\Setup\Fixtures\Quote\QuoteConfiguration class.
 */
class QuoteConfigurationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Fixtures\FixtureModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\Quote\QuoteConfiguration
     */
    private $fixture;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder(\Magento\Setup\Fixtures\FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->fixture = $objectManager->getObject(
            \Magento\Setup\Fixtures\Quote\QuoteConfiguration::class,
            [
                'fixtureModel' => $this->fixtureModelMock
            ]
        );
    }

    /**
     * Test load method.
     *
     * @return void
     */
    public function testLoad()
    {
        $dir = str_replace('Test/Unit/', '', dirname(__DIR__));
        $expectedResult = [
            'simple_count_to' => 1,
            'simple_count_from' => 1,
            'configurable_count_to' => 1,
            'configurable_count_from' => 1,
            'big_configurable_count_to' => 1,
            'big_configurable_count_from' => 1,
            'fixture_data_filename' =>
                $dir . DIRECTORY_SEPARATOR . "_files" . DIRECTORY_SEPARATOR . 'orders_fixture_data.json',
            'order_quotes_enable' => 1,
        ];
        $this->fixtureModelMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->withConsecutive(
                ['order_simple_product_count_to'],
                ['order_simple_product_count_from'],
                ['order_configurable_product_count_to'],
                ['order_configurable_product_count_from'],
                ['order_big_configurable_product_count_to'],
                ['order_big_configurable_product_count_from'],
                ['order_quotes_enable',]
            )->willReturn(1);
        $this->assertSame($expectedResult, $this->fixture->load()->getData());
    }
}
