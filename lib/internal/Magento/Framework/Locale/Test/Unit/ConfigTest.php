<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Locale\Test\Unit;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    private static $allAllowedLocales = [
        'af_ZA', 'ar_DZ', 'ar_EG', 'ar_KW', 'ar_MA', 'ar_SA', 'az_Latn_AZ', 'be_BY', 'bg_BG', 'bn_BD',
        'bs_Latn_BA', 'ca_ES', 'cs_CZ', 'cy_GB', 'da_DK', 'de_AT', 'de_CH', 'de_DE', 'el_GR', 'en_AU',
        'en_CA', 'en_GB', 'en_NZ', 'en_US', 'es_AR', 'es_CO', 'es_PA', 'gl_ES', 'es_CR', 'es_ES',
        'es_MX', 'eu_ES', 'es_PE', 'et_EE', 'fa_IR', 'fi_FI', 'fil_PH', 'fr_CA', 'fr_FR', 'gu_IN',
        'he_IL', 'hi_IN', 'hr_HR', 'hu_HU', 'id_ID', 'is_IS', 'it_CH', 'it_IT', 'ja_JP', 'ka_GE',
        'km_KH', 'ko_KR', 'lo_LA', 'lt_LT', 'lv_LV', 'mk_MK', 'mn_Cyrl_MN', 'ms_Latn_MY', 'nl_NL', 'nb_NO',
        'nn_NO', 'pl_PL', 'pt_BR', 'pt_PT', 'ro_RO', 'ru_RU', 'sk_SK', 'sl_SI', 'sq_AL', 'sr_Cyrl_RS',
        'sv_SE', 'sw_KE', 'th_TH', 'tr_TR', 'uk_UA', 'vi_VN', 'zh_Hans_CN', 'zh_Hant_HK', 'zh_Hant_TW', 'es_CL',
        'lo_LA', 'es_VE', 'en_IE',
    ];

    private static $allAllowedCurrencies = [
        'AFN', 'ALL', 'AZN', 'DZD', 'AOA', 'ARS', 'AMD', 'AWG', 'AUD', 'BSD',
        'BHD', 'BDT', 'BBD', 'BYR', 'BZD', 'BMD', 'BTN', 'BOB', 'BAM', 'BWP',
        'BRL', 'GBP', 'BND', 'BGN', 'BUK', 'BIF', 'KHR', 'CAD', 'CVE', 'CZK',
        'KYD', 'GQE', 'CLP', 'CNY', 'COP', 'KMF', 'CDF', 'CRC', 'HRK', 'CUP',
        'DKK', 'DJF', 'DOP', 'XCD', 'EGP', 'SVC', 'ERN', 'EEK', 'ETB', 'EUR',
        'FKP', 'FJD', 'GMD', 'GEK', 'GEL', 'GHS', 'GIP', 'GTQ', 'GNF', 'GYD',
        'HTG', 'HNL', 'HKD', 'HUF', 'ISK', 'INR', 'IDR', 'IRR', 'IQD', 'ILS',
        'JMD', 'JPY', 'JOD', 'KZT', 'KES', 'KWD', 'KGS', 'LAK', 'LVL', 'LBP',
        'LSL', 'LRD', 'LYD', 'LTL', 'MOP', 'MKD', 'MGA', 'MWK', 'MYR', 'MVR',
        'LSM', 'MRO', 'MUR', 'MXN', 'MDL', 'MNT', 'MAD', 'MZN', 'MMK', 'NAD',
        'NPR', 'ANG', 'YTL', 'NZD', 'NIC', 'NGN', 'KPW', 'NOK', 'OMR', 'PKR',
        'PAB', 'PGK', 'PYG', 'PEN', 'PHP', 'PLN', 'QAR', 'RHD', 'RON', 'RUB',
        'RWF', 'SHP', 'STD', 'SAR', 'RSD', 'SCR', 'SLL', 'SGD', 'SKK', 'SBD',
        'SOS', 'ZAR', 'KRW', 'LKR', 'SDG', 'SRD', 'SZL', 'SEK', 'CHF', 'SYP',
        'TWD', 'TJS', 'TZS', 'THB', 'TOP', 'TTD', 'TND', 'TMM', 'USD', 'UGX',
        'UAH', 'AED', 'UYU', 'UZS', 'VUV', 'VEB', 'VEF', 'VND', 'CHE', 'CHW',
        'XOF', 'WST', 'YER', 'ZMK', 'ZWD', 'TRY', 'AZM', 'ROL', 'TRL', 'XPF',
    ];

    private static $samplePresentLocales = [
        'en_US', 'lv_LV', 'pt_BR', 'it_IT', 'ar_EG', 'bg_BG', 'en_IE', 'es_ES',
        'en_AU', 'pt_PT', 'ru_RU', 'en_CA', 'vi_VN', 'ja_JP', 'en_GB', 'zh_CN',
        'zh_TW', 'fr_FR', 'ar_KW', 'pl_PL', 'ko_KR', 'sk_SK', 'el_GR', 'hi_IN',
    ];

    private static $sampleAbsentLocales = [
        'aa_BB', 'foo_BAR', 'cc_DD',
    ];

    private static $sampleAdditionalLocales = [
        'en_AA', 'es_ZZ',
    ];

    private static $samplePresentCurrencies = [
        'AUD', 'BBD', 'GBP', 'CAD', 'CZK', 'GQE', 'CNY', 'DJF', 'HKD', 'JPY', 'MYR',
        'MXN', 'NZD', 'PHP', 'SGD', 'CHF', 'TWD', 'USD', 'AED', 'ZWD', 'ROL', 'CHE',
    ];

    private static $sampleAbsentCurrencies = [
        'ABC', 'DEF', 'GHI', 'ZZZ',
    ];

    private static $sampleAdditionalCurrencies = [
        'QED', 'PNP', 'EJN', 'MTO', 'EBY',
    ];

    /** @var  \Magento\Framework\Locale\Config */
    private $configObject;

    public function testGetAllowedLocalesNoDataArray()
    {
        $this->configObject = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Framework\Locale\Config::class);

        $retrievedLocales = $this->configObject->getAllowedLocales();

        $differences = array_diff($this::$allAllowedLocales, $retrievedLocales);

        $this->assertEmpty($differences);

        foreach ($this::$sampleAbsentLocales as $absentLocale) {
            $this->assertNotContains($absentLocale, $retrievedLocales);
        }
    }

    public function testGetAllowedLocalesGivenDataArray()
    {
        $this->configObject = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Framework\Locale\Config::class,
                [
                    'data' => [
                        'allowedLocales' => $this::$sampleAdditionalLocales,
                    ],
                ]
            );

        $retrievedLocalesWithAdditions = $this->configObject->getAllowedLocales();

        $differences = array_diff(
            array_unique(array_merge($this::$allAllowedLocales, $this::$sampleAdditionalLocales)),
            $retrievedLocalesWithAdditions
        );

        $this->assertEmpty($differences);

        foreach ($this::$sampleAbsentLocales as $absentLocale) {
            $this->assertNotContains($absentLocale, $retrievedLocalesWithAdditions);
        }
    }

    public function testGetAllowedLocalesGivenRedundantDataArray()
    {
        $this->configObject = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Framework\Locale\Config::class,
                [
                    'data' => [
                        'allowedLocales' => $this::$samplePresentLocales,
                    ],
                ]
            );

        $retrievedLocalesWithRedundencies = $this->configObject->getAllowedLocales();

        $differences = array_diff(
            $this::$allAllowedLocales,
            $retrievedLocalesWithRedundencies
        );

        $this->assertEmpty($differences);

        foreach ($this::$sampleAbsentLocales as $absentLocale) {
            $this->assertNotContains($absentLocale, $retrievedLocalesWithRedundencies);
        }
    }

    public function testGetAllowedCurrenciesNoDataArray()
    {
        $this->configObject = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Framework\Locale\Config::class);

        $retrievedCurrencies = $this->configObject->getAllowedCurrencies();

        $differences = array_diff($this::$allAllowedCurrencies, $retrievedCurrencies);

        $this->assertEmpty($differences);

        foreach ($this::$sampleAbsentCurrencies as $absentCurrency) {
            $this->assertNotContains($absentCurrency, $retrievedCurrencies);
        }
    }

    public function testGetAllowedCurrenciesGivenDataArray()
    {
        $this->configObject = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Framework\Locale\Config::class,
                [
                    'data' => [
                        'allowedCurrencies' => $this::$sampleAdditionalCurrencies,
                    ],
                ]
            );

        $retrievedCurrenciesWithAdditions = $this->configObject->getAllowedCurrencies();

        $differences = array_diff(
            array_unique(array_merge($this::$allAllowedCurrencies, $this::$samplePresentCurrencies)),
            $retrievedCurrenciesWithAdditions
        );

        $this->assertEmpty($differences);

        foreach ($this::$sampleAbsentCurrencies as $absentCurrency) {
            $this->assertNotContains($absentCurrency, $retrievedCurrenciesWithAdditions);
        }
    }

    public function testGetAllowedCurrenciesGivenRedundantDataArray()
    {
        $this->configObject = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Framework\Locale\Config::class,
                [
                    'data' => [
                        'allowedCurrencies' => $this::$samplePresentCurrencies,
                    ],
                ]
            );

        $retrievedCurrenciesWithRedundencies = $this->configObject->getAllowedCurrencies();

        $differences = array_diff(
            $this::$allAllowedCurrencies,
            $retrievedCurrenciesWithRedundencies
        );

        $this->assertEmpty($differences);

        foreach ($this::$sampleAbsentCurrencies as $absentCurrency) {
            $this->assertNotContains($absentCurrency, $retrievedCurrenciesWithRedundencies);
        }
    }
}
