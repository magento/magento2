<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Model;

use Magento\TestFramework\Helper\ObjectManager;

class ListsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Lists
     */
    private $lists;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->lists = new Lists(
            $objectManager->getObject('Zend_Locale'),
            $objectManager->getObject('Magento\Framework\Locale\Config')
        );
    }

    public function testGetTimezoneList()
    {
        $expected = [
            'Australia/Darwin' => 'AUS Central Standard Time (Australia/Darwin)',
            'America/Regina' => 'Canada Central Standard Time (America/Regina)',
            'Europe/Kiev' => 'FLE Standard Time (Europe/Kiev)',
            'Africa/Windhoek' => 'Namibia Standard Time (Africa/Windhoek)',
            'Asia/Katmandu' => 'Nepal Standard Time (Asia/Katmandu)',
            'Etc/GMT+2' => 'UTC-02 (Etc/GMT+2)',
            'Pacific/Port_Moresby' => 'West Pacific Standard Time (Pacific/Port_Moresby)',
            'Asia/Yakutsk' => 'Yakutsk Standard Time (Asia/Yakutsk)',
        ];

        $this->assertTrue(array_intersect_assoc($expected, $this->lists->getTimezoneList()) == $expected);
    }

    public function testGetCurrencyList()
    {
        $expected = [
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
        $this->assertTrue(array_intersect_assoc($expected, $this->lists->getCurrencyList()) == $expected);
    }

    public function testGetLocaleList()
    {
        $expected = [
            'aa_DJ' => 'Afar (Djibouti)',
            'ar_ER' => 'Arabic (Eritrea)',
            'ar_TN' => 'Arabic (Tunisia)',
            'bn_BD' => 'Bengali (Bangladesh)',
            'bn_IN' => 'Bengali (India)',
            'byn_ER' => 'Blin (Eritrea)',
            'brx_IN' => 'Bodo (India)',
            'zh_CN' => 'Chinese (China)',
            'zh_HK' => 'Chinese (Hong Kong SAR China)',
            'nl_NL' => 'Dutch (Netherlands)',
            'nl_SX' => 'Dutch (Sint Maarten)',
            'en_BW' => 'English (Botswana)',
            'fr_BJ' => 'French (Benin)',
            'fr_BF' => 'French (Burkina Faso)',
            'ia_FR' => 'Interlingua (France)',
            'ga_IE' => 'Irish (Ireland)',
            'it_IT' => 'Italian (Italy)',
            'lag_TZ' => 'Langi (Tanzania)',
            'lo_LA' => 'Lao (Laos)',
            'lv_LV' => 'Latvian (Latvia)',
            'ln_AO' => 'Lingala (Angola)',
            'nso_ZA' => 'Northern Sotho (South Africa)',
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
            'fy_NL' => 'Western Frisian (Netherlands)',
            'wal_ET' => 'Wolaytta (Ethiopia)',
            'xh_ZA' => 'Xhosa (South Africa)',
            'yav_CM' => 'Yangben (Cameroon)',
            'yo_BJ' => 'Yoruba (Benin)',
            'yo_NG' => 'Yoruba (Nigeria)',
            'dje_NE' => 'Zarma (Niger)',
            'zu_ZA' => 'Zulu (South Africa)',
        ];

        $this->assertTrue(array_intersect_assoc($expected, $this->lists->getLocaleList()) == $expected);
    }
}
