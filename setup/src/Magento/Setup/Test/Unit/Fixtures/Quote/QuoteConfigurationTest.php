<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Fixtures\Quote\QuoteConfiguration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Setup\Fixtures\Quote\QuoteConfiguration class.
 */
class QuoteConfigurationTest extends TestCase
{
    /**
     * @var FixtureModel|MockObject
     */
    private $fixtureModelMock;

    /**
     * @var QuoteConfiguration
     */
    private $fixture;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->getMockBuilder(FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);

        $this->fixture = $objectManager->getObject(
            QuoteConfiguration::class,
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
            'fixture_data_filename' => $dir . DIRECTORY_SEPARATOR . "_files"
                . DIRECTORY_SEPARATOR . 'orders_fixture_data.json',
            'order_quotes_enable' => 1,
        ];
        $this->fixtureModelMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnCallback(
                function ($arg) {
                    if ($arg == 'order_simple_product_count_to') {
                        return 1;
                    } elseif ($arg == 'order_simple_product_count_from') {
                        return 1;
                    } elseif ($arg == 'order_configurable_product_count_to') {
                        return 1;
                    } elseif ($arg == 'order_configurable_product_count_from') {
                        return 1;
                    } elseif ($arg == 'order_big_configurable_product_count_to') {
                        return 1;
                    } elseif ($arg == 'order_big_configurable_product_count_from') {
                        return 1;
                    } elseif ($arg == 'order_quotes_enable') {
                        return 1;
                    }
                }
            );
        $this->assertSame($expectedResult, $this->fixture->load()->getData());
    }
}
