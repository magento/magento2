<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\TestFramework\Helper\ObjectManager;

class ListsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Lists
     */
    protected $lists;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Locale\ConfigInterface
     */
    protected $mockConfig;

    /**
     * @var array
     */
    protected $expectedTimezones = [
        'Australia/Darwin' => 'Australian Central Standard Time (Australia/Darwin)',
        'America/Los_Angeles' => 'Pacific Standard Time (America/Los_Angeles)',
        'Europe/Kiev' => 'Eastern European Standard Time (Europe/Kiev)',
        'Asia/Jerusalem' => 'Israel Standard Time (Asia/Jerusalem)',
        'Asia/Yakutsk' => 'Yakutsk Standard Time (Asia/Yakutsk)',
    ];

    /**
     * @var array
     */
    protected $expectedCurrencies = [
        'XUA' => 'ADB Unit of Account (XUA)',
        'AFA' => 'Afghan Afghani (1927–2002) (AFA)',
        'AZM' => 'Azerbaijani Manat (1993–2006) (AZM)',
        'AZN' => 'Azerbaijani Manat (AZN)',
        'BOB' => 'Bolivian Boliviano (BOB)',
        'CUC' => 'Cuban Convertible Peso (CUC)',
        'CUP' => 'Cuban Peso (CUP)',
        'CYP' => 'Cypriot Pound (CYP)',
        'CZK' => 'Czech Republic Koruna (CZK)',
        'CSK' => 'Czechoslovak Hard Koruna (CSK)',
        'DKK' => 'Danish Krone (DKK)',
        'ZRN' => 'Zairean New Zaire (1993–1998) (ZRN)',
        'ZRZ' => 'Zairean Zaire (1971–1993) (ZRZ)',
        'ZMK' => 'Zambian Kwacha (1968–2012) (ZMK)',
        'ZMW' => 'Zambian Kwacha (ZMW)',
        'ZWD' => 'Zimbabwean Dollar (1980–2008) (ZWD)',
    ];

    /**
     * @var array
     */
    protected $expectedLocales = [
        'ar_DJ' => 'Arabic (Djibouti)',
        'ar_ER' => 'Arabic (Eritrea)',
        'ar_TN' => 'Arabic (Tunisia)',
        'bn_BD' => 'Bengali (Bangladesh)',
        'bn_IN' => 'Bengali (India)',
        'brx_IN' => 'Bodo (India)',
        'zh_Hans_CN' => 'Chinese (China)',
        'zh_Hant_HK' => 'Chinese (Hong Kong SAR China)',
        'nl_NL' => 'Dutch (Netherlands)',
        'nl_SX' => 'Dutch (Sint Maarten)',
        'en_BW' => 'English (Botswana)',
        'fr_BJ' => 'French (Benin)',
        'fr_BF' => 'French (Burkina Faso)',
        'ga_IE' => 'Irish (Ireland)',
        'it_IT' => 'Italian (Italy)',
        'lag_TZ' => 'Langi (Tanzania)',
        'lo_LA' => 'Lao (Laos)',
        'lv_LV' => 'Latvian (Latvia)',
        'ln_AO' => 'Lingala (Angola)',
        'pt_TL' => 'Portuguese (Timor-Leste)',
        'ro_MD' => 'Romanian (Moldova)',
        'ro_RO' => 'Romanian (Romania)',
        'rm_CH' => 'Romansh (Switzerland)',
        'rof_TZ' => 'Rombo (Tanzania)',
        'rn_BI' => 'Rundi (Burundi)',
        'ru_UA' => 'Russian (Ukraine)',
        'rwk_TZ' => 'Rwa (Tanzania)',
        'so_ET' => 'Somali (Ethiopia)',
        'es_ES' => 'Spanish (Spain)',
        'es_US' => 'Spanish (United States)',
        'teo_UG' => 'Teso (Uganda)',
        'th_TH' => 'Thai (Thailand)',
        'bo_CN' => 'Tibetan (China)',
        'yav_CM' => 'Yangben (Cameroon)',
        'yo_BJ' => 'Yoruba (Benin)',
        'yo_NG' => 'Yoruba (Nigeria)',
        'dje_NE' => 'Zarma (Niger)',
        'zu_ZA' => 'Zulu (South Africa)',
    ];

    public function setUp()
    {
        $this->mockConfig = $this->getMockBuilder('\Magento\Framework\Locale\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig->expects($this->any())
            ->method('getAllowedLocales')
            ->willReturn(array_keys($this->expectedLocales));

        $this->lists = new Lists($this->mockConfig);
    }

    public function testGetTimezoneList()
    {
        $timezones = array_intersect_assoc($this->expectedTimezones, $this->lists->getTimezoneList());
        $this->assertEquals($this->expectedTimezones, $timezones);
    }

    public function testGetCurrencyList()
    {
        $currencies = array_intersect_assoc($this->expectedCurrencies, $this->lists->getCurrencyList());
        $this->assertEquals($this->expectedCurrencies, $currencies);
    }

    public function testGetLocaleList()
    {
        $locales = array_intersect_assoc($this->expectedLocales, $this->lists->getLocaleList());
        $this->assertEquals($this->expectedLocales, $locales);
    }
}
