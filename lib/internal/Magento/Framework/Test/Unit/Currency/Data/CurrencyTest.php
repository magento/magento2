<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Currency\Data;

use Magento\Framework\Currency\Data\Currency;
use Magento\Framework\Currency\Exception\CurrencyException;
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
     * @throws CurrencyException
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
    public static function optionsDataProvider(): array
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
            'format_ar_MA_MAD' => [
                'value' => 3,
                'options' => [
                    'locale' => 'ar_MA',
                    'currency' => 'MAD',
                ],
                'expectedResult' => self::expectedMadFormat(),
            ],
        ];
    }

    /**
     * Check if the current intl extension version uses newer formatting rules
     *
     * @return bool
     */
    private static function isNewerIntlVersion(): bool
    {
        // Check intl extension version
        if (extension_loaded('intl')) {
            $intlVersion = INTL_ICU_VERSION ?? '0.0.0';

            // ICU 72+ (released around 2022) introduced changes to RTL formatting
            // This is a more reliable indicator than PHP version
            return version_compare($intlVersion, '72.0', '>=');
        }

        // Fallback: Check PHP version as a rough indicator
        // This is less reliable but provides some backward compatibility
        return version_compare(PHP_VERSION, '8.3', '>=');
    }

    private static function expectedMadFormat(): string
    {
        if (self::isNewerIntlVersion()) {
            return "\u{200F}3,00\u{00A0}\u{062F}.\u{0645}.\u{200F}";
        }
        return "\u{062F}.\u{0645}.\u{200F}\u{00A0}3,00";
    }
}
