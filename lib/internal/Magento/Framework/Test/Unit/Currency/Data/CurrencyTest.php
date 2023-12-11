<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Currency\Data;

use Magento\Framework\Currency\Data\Currency;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Framework\Currency\Data\Currency
 */
class CurrencyTest extends TestCase
{
    /**
     * @param float|int|null $value
     * @param array $options
     * @param string $expectedResult
     * @return void
     * @throws \Magento\Framework\Currency\Exception\CurrencyException
     *
     * @dataProvider optionsDataProvider
     */
    public function testToCurrencyWithOptions(float|int|null $value, array $options, string $expectedResult): void
    {
        $currency = new Currency();
        $result = $currency->toCurrency($value, $options);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array[]
     */
    public function optionsDataProvider(): array
    {
        return [
            'rightPosition_en_AU' => [
                'value' => 3,
                'options' => [
                    'position' => Currency::RIGHT,
                    'locale' => 'en_AU',
                    'currency' => 'AUD',
                ],
                'expectedResult' => '3.00$',
            ],
            'leftPosition_en_AU' => [
                'value' => 3,
                'options' => [
                    'position' => Currency::LEFT,
                    'locale' => 'en_AU',
                    'currency' => 'AUD',
                ],
                'expectedResult' => '$3.00',
            ],
            'defaultPosition_en_AU' => [
                'value' => 22,
                'options' => [
                    'locale' => 'en_AU',
                    'currency' => 'AUD',
                ],
                'expectedResult' => '$22.00',
            ],

            'rightPosition_CUST_en_US' => [
                'value' => 12,
                'options' => [
                    'position' => Currency::RIGHT,
                    'locale' => 'en_US',
                    'symbol' => 'CUST',
                ],
                'expectedResult' => '12.00CUST',
            ],
            'leftPosition_CUST_en_US' => [
                'value' => 12,
                'options' => [
                    'position' => Currency::LEFT,
                    'locale' => 'en_US',
                    'symbol' => 'CUST',
                ],
                'expectedResult' => 'CUST12.00',
            ],
            'rightPosition_CUST_with_space_zu_ZA' => [
                'value' => 12,
                'options' => [
                    'position' => Currency::RIGHT,
                    'locale' => 'zu_ZA',
                    'symbol' => 'CUST',
                ],
                'expectedResult' => '12.00 CUST',
            ],
            'leftPosition_CUST_with_space_zu_ZA' => [
                'value' => 12,
                'options' => [
                    'position' => Currency::LEFT,
                    'locale' => 'zu_ZA',
                    'symbol' => 'CUST',
                ],
                'expectedResult' => 'CUST 12.00',
            ],
            'precisionIsGreaterThanZero' => [
                'value' => 12.16,
                'options' => [
                    'locale' => 'en_US',
                    'currency' => 'USD',
                    'precision'=> 1,
                ],
                'expectedResult' => '$12.2',
            ],
            'precisionIsZero' => [
                'value' => 12.16,
                'options' => [
                    'locale' => 'en_US',
                    'currency' => 'USD',
                    'precision'=> 0,
                ],
                'expectedResult' => '$12',
            ],
        ];
    }
}
