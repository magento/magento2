<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category  Zend
 * @package   Zend_Locale
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Locale.php 22712 2010-07-29 08:24:28Z thomas $
 */

/**
 * Base class for localization
 *
 * @category  Zend
 * @package   Zend_Locale
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Locale
{
    /**
     * Class wide Locale Constants
     *
     * @var array $_localeData
     */
    private static $_localeData = array(
        'root'  => true, 'aa_DJ' => true, 'aa_ER' => true, 'aa_ET' => true, 'aa'    => true,
        'af_NA' => true, 'af_ZA' => true, 'af'    => true, 'ak_GH' => true, 'ak'    => true,
        'am_ET' => true, 'am'    => true, 'ar_AE' => true, 'ar_BH' => true, 'ar_DZ' => true,
        'ar_EG' => true, 'ar_IQ' => true, 'ar_JO' => true, 'ar_KW' => true, 'ar_LB' => true,
        'ar_LY' => true, 'ar_MA' => true, 'ar_OM' => true, 'ar_QA' => true, 'ar_SA' => true,
        'ar_SD' => true, 'ar_SY' => true, 'ar_TN' => true, 'ar_YE' => true, 'ar'    => true,
        'as_IN' => true, 'as'    => true, 'az_AZ' => true, 'az'    => true, 'be_BY' => true,
        'be'    => true, 'bg_BG' => true, 'bg'    => true, 'bn_BD' => true, 'bn_IN' => true,
        'bn'    => true, 'bo_CN' => true, 'bo_IN' => true, 'bo'    => true, 'bs_BA' => true,
        'bs'    => true, 'byn_ER'=> true, 'byn'   => true, 'ca_ES' => true, 'ca'    => true,
        'cch_NG'=> true, 'cch'   => true, 'cop'   => true, 'cs_CZ' => true, 'cs'    => true,
        'cy_GB' => true, 'cy'    => true, 'da_DK' => true, 'da'    => true, 'de_AT' => true,
        'de_BE' => true, 'de_CH' => true, 'de_DE' => true, 'de_LI' => true, 'de_LU' => true,
        'de'    => true, 'dv_MV' => true, 'dv'    => true, 'dz_BT' => true, 'dz'    => true,
        'ee_GH' => true, 'ee_TG' => true, 'ee'    => true, 'el_CY' => true, 'el_GR' => true,
        'el'    => true, 'en_AS' => true, 'en_AU' => true, 'en_BE' => true, 'en_BW' => true,
        'en_BZ' => true, 'en_CA' => true, 'en_GB' => true, 'en_GU' => true, 'en_HK' => true,
        'en_IE' => true, 'en_IN' => true, 'en_JM' => true, 'en_MH' => true, 'en_MP' => true,
        'en_MT' => true, 'en_NA' => true, 'en_NZ' => true, 'en_PH' => true, 'en_PK' => true,
        'en_SG' => true, 'en_TT' => true, 'en_UM' => true, 'en_US' => true, 'en_VI' => true,
        'en_ZA' => true, 'en_ZW' => true, 'en'    => true, 'eo'    => true, 'es_AR' => true,
        'es_BO' => true, 'es_CL' => true, 'es_CO' => true, 'es_CR' => true, 'es_DO' => true,
        'es_EC' => true, 'es_ES' => true, 'es_GT' => true, 'es_HN' => true, 'es_MX' => true,
        'es_NI' => true, 'es_PA' => true, 'es_PE' => true, 'es_PR' => true, 'es_PY' => true,
        'es_SV' => true, 'es_US' => true, 'es_UY' => true, 'es_VE' => true, 'es'    => true,
        'et_EE' => true, 'et'    => true, 'eu_ES' => true, 'eu'    => true, 'fa_AF' => true,
        'fa_IR' => true, 'fa'    => true, 'fi_FI' => true, 'fi'    => true, 'fil_PH'=> true,
        'fil'   => true, 'fo_FO' => true, 'fo'    => true, 'fr_BE' => true, 'fr_CA' => true,
        'fr_CH' => true, 'fr_FR' => true, 'fr_LU' => true, 'fr_MC' => true, 'fr_SN' => true,
        'fr'    => true, 'fur_IT'=> true, 'fur'   => true, 'ga_IE' => true, 'ga'    => true,
        'gaa_GH'=> true, 'gaa'   => true, 'gez_ER'=> true, 'gez_ET'=> true, 'gez'   => true,
        'gl_ES' => true, 'gl'    => true, 'gsw_CH'=> true, 'gsw'   => true, 'gu_IN' => true,
        'gu'    => true, 'gv_GB' => true, 'gv'    => true, 'ha_GH' => true, 'ha_NE' => true,
        'ha_NG' => true, 'ha_SD' => true, 'ha'    => true, 'haw_US'=> true, 'haw'   => true,
        'he_IL' => true, 'he'    => true, 'hi_IN' => true, 'hi'    => true, 'hr_HR' => true,
        'hr'    => true, 'hu_HU' => true, 'hu'    => true, 'hy_AM' => true, 'hy'    => true,
        'ia'    => true, 'id_ID' => true, 'id'    => true, 'ig_NG' => true, 'ig'    => true,
        'ii_CN' => true, 'ii'    => true, 'in'    => true, 'is_IS' => true, 'is'    => true,
        'it_CH' => true, 'it_IT' => true, 'it'    => true, 'iu'    => true, 'iw'    => true,
        'ja_JP' => true, 'ja'    => true, 'ka_GE' => true, 'ka'    => true, 'kaj_NG'=> true,
        'kaj'   => true, 'kam_KE'=> true, 'kam'   => true, 'kcg_NG'=> true, 'kcg'   => true,
        'kfo_CI'=> true, 'kfo'   => true, 'kk_KZ' => true, 'kk'    => true, 'kl_GL' => true,
        'kl'    => true, 'km_KH' => true, 'km'    => true, 'kn_IN' => true, 'kn'    => true,
        'ko_KR' => true, 'ko'    => true, 'kok_IN'=> true, 'kok'   => true, 'kpe_GN'=> true,
        'kpe_LR'=> true, 'kpe'   => true, 'ku_IQ' => true, 'ku_IR' => true, 'ku_SY' => true,
        'ku_TR' => true, 'ku'    => true, 'kw_GB' => true, 'kw'    => true, 'ky_KG' => true,
        'ky'    => true, 'ln_CD' => true, 'ln_CG' => true, 'ln'    => true, 'lo_LA' => true,
        'lo'    => true, 'lt_LT' => true, 'lt'    => true, 'lv_LV' => true, 'lv'    => true,
        'mk_MK' => true, 'mk'    => true, 'ml_IN' => true, 'ml'    => true, 'mn_CN' => true,
        'mn_MN' => true, 'mn'    => true, 'mo'    => true, 'mr_IN' => true, 'mr'    => true,
        'ms_BN' => true, 'ms_MY' => true, 'ms'    => true, 'mt_MT' => true, 'mt'    => true,
        'my_MM' => true, 'my'    => true, 'nb_NO' => true, 'nb'    => true, 'nds_DE'=> true,
        'nds'   => true, 'ne_IN' => true, 'ne_NP' => true, 'ne'    => true, 'nl_BE' => true,
        'nl_NL' => true, 'nl'    => true, 'nn_NO' => true, 'nn'    => true, 'no'    => true,
        'nr_ZA' => true, 'nr'    => true, 'nso_ZA'=> true, 'nso'   => true, 'ny_MW' => true,
        'ny'    => true, 'oc_FR' => true, 'oc'    => true, 'om_ET' => true, 'om_KE' => true,
        'om'    => true, 'or_IN' => true, 'or'    => true, 'pa_IN' => true, 'pa_PK' => true,
        'pa'    => true, 'pl_PL' => true, 'pl'    => true, 'ps_AF' => true, 'ps'    => true,
        'pt_BR' => true, 'pt_PT' => true, 'pt'    => true, 'ro_MD' => true, 'ro_RO' => true,
        'ro'    => true, 'ru_RU' => true, 'ru_UA' => true, 'ru'    => true, 'rw_RW' => true,
        'rw'    => true, 'sa_IN' => true, 'sa'    => true, 'se_FI' => true, 'se_NO' => true,
        'se'    => true, 'sh_BA' => true, 'sh_CS' => true, 'sh_YU' => true, 'sh'    => true,
        'si_LK' => true, 'si'    => true, 'sid_ET'=> true, 'sid'   => true, 'sk_SK' => true,
        'sk'    => true, 'sl_SI' => true, 'sl'    => true, 'so_DJ' => true, 'so_ET' => true,
        'so_KE' => true, 'so_SO' => true, 'so'    => true, 'sq_AL' => true, 'sq'    => true,
        'sr_BA' => true, 'sr_CS' => true, 'sr_ME' => true, 'sr_RS' => true, 'sr_YU' => true,
        'sr'    => true, 'ss_SZ' => true, 'ss_ZA' => true, 'ss'    => true, 'st_LS' => true,
        'st_ZA' => true, 'st'    => true, 'sv_FI' => true, 'sv_SE' => true, 'sv'    => true,
        'sw_KE' => true, 'sw_TZ' => true, 'sw'    => true, 'syr_SY'=> true, 'syr'   => true,
        'ta_IN' => true, 'ta'    => true, 'te_IN' => true, 'te'    => true, 'tg_TJ' => true,
        'tg'    => true, 'th_TH' => true, 'th'    => true, 'ti_ER' => true, 'ti_ET' => true,
        'ti'    => true, 'tig_ER'=> true, 'tig'   => true, 'tl'    => true, 'tn_ZA' => true,
        'tn'    => true, 'to_TO' => true, 'to'    => true, 'tr_TR' => true, 'tr'    => true,
        'trv_TW'=> true, 'trv'   => true, 'ts_ZA' => true, 'ts'    => true, 'tt_RU' => true,
        'tt'    => true, 'ug_CN' => true, 'ug'    => true, 'uk_UA' => true, 'uk'    => true,
        'ur_IN' => true, 'ur_PK' => true, 'ur'    => true, 'uz_AF' => true, 'uz_UZ' => true,
        'uz'    => true, 've_ZA' => true, 've'    => true, 'vi_VN' => true, 'vi'    => true,
        'wal_ET'=> true, 'wal'   => true, 'wo_SN' => true, 'wo'    => true, 'xh_ZA' => true,
        'xh'    => true, 'yo_NG' => true, 'yo'    => true, 'zh_CN' => true, 'zh_HK' => true,
        'zh_MO' => true, 'zh_SG' => true, 'zh_TW' => true, 'zh'    => true, 'zu_ZA' => true,
        'zu'    => true
    );

    /**
     * Class wide Locale Constants
     *
     * @var array $_territoryData
     */
    private static $_territoryData = array(
        'AD' => 'ca_AD', 'AE' => 'ar_AE', 'AF' => 'fa_AF', 'AG' => 'en_AG', 'AI' => 'en_AI',
        'AL' => 'sq_AL', 'AM' => 'hy_AM', 'AN' => 'pap_AN', 'AO' => 'pt_AO', 'AQ' => 'und_AQ',
        'AR' => 'es_AR', 'AS' => 'sm_AS', 'AT' => 'de_AT', 'AU' => 'en_AU', 'AW' => 'nl_AW',
        'AX' => 'sv_AX', 'AZ' => 'az_Latn_AZ', 'BA' => 'bs_BA', 'BB' => 'en_BB', 'BD' => 'bn_BD',
        'BE' => 'nl_BE', 'BF' => 'mos_BF', 'BG' => 'bg_BG', 'BH' => 'ar_BH', 'BI' => 'rn_BI',
        'BJ' => 'fr_BJ', 'BL' => 'fr_BL', 'BM' => 'en_BM', 'BN' => 'ms_BN', 'BO' => 'es_BO',
        'BR' => 'pt_BR', 'BS' => 'en_BS', 'BT' => 'dz_BT', 'BV' => 'und_BV', 'BW' => 'en_BW',
        'BY' => 'be_BY', 'BZ' => 'en_BZ', 'CA' => 'en_CA', 'CC' => 'ms_CC', 'CD' => 'sw_CD',
        'CF' => 'fr_CF', 'CG' => 'fr_CG', 'CH' => 'de_CH', 'CI' => 'fr_CI', 'CK' => 'en_CK',
        'CL' => 'es_CL', 'CM' => 'fr_CM', 'CN' => 'zh_Hans_CN', 'CO' => 'es_CO', 'CR' => 'es_CR',
        'CU' => 'es_CU', 'CV' => 'kea_CV', 'CX' => 'en_CX', 'CY' => 'el_CY', 'CZ' => 'cs_CZ',
        'DE' => 'de_DE', 'DJ' => 'aa_DJ', 'DK' => 'da_DK', 'DM' => 'en_DM', 'DO' => 'es_DO',
        'DZ' => 'ar_DZ', 'EC' => 'es_EC', 'EE' => 'et_EE', 'EG' => 'ar_EG', 'EH' => 'ar_EH',
        'ER' => 'ti_ER', 'ES' => 'es_ES', 'ET' => 'en_ET', 'FI' => 'fi_FI', 'FJ' => 'hi_FJ',
        'FK' => 'en_FK', 'FM' => 'chk_FM', 'FO' => 'fo_FO', 'FR' => 'fr_FR', 'GA' => 'fr_GA',
        'GB' => 'en_GB', 'GD' => 'en_GD', 'GE' => 'ka_GE', 'GF' => 'fr_GF', 'GG' => 'en_GG',
        'GH' => 'ak_GH', 'GI' => 'en_GI', 'GL' => 'iu_GL', 'GM' => 'en_GM', 'GN' => 'fr_GN',
        'GP' => 'fr_GP', 'GQ' => 'fan_GQ', 'GR' => 'el_GR', 'GS' => 'und_GS', 'GT' => 'es_GT',
        'GU' => 'en_GU', 'GW' => 'pt_GW', 'GY' => 'en_GY', 'HK' => 'zh_Hant_HK', 'HM' => 'und_HM',
        'HN' => 'es_HN', 'HR' => 'hr_HR', 'HT' => 'ht_HT', 'HU' => 'hu_HU', 'ID' => 'id_ID',
        'IE' => 'en_IE', 'IL' => 'he_IL', 'IM' => 'en_IM', 'IN' => 'hi_IN', 'IO' => 'und_IO',
        'IQ' => 'ar_IQ', 'IR' => 'fa_IR', 'IS' => 'is_IS', 'IT' => 'it_IT', 'JE' => 'en_JE',
        'JM' => 'en_JM', 'JO' => 'ar_JO', 'JP' => 'ja_JP', 'KE' => 'en_KE', 'KG' => 'ky_Cyrl_KG',
        'KH' => 'km_KH', 'KI' => 'en_KI', 'KM' => 'ar_KM', 'KN' => 'en_KN', 'KP' => 'ko_KP',
        'KR' => 'ko_KR', 'KW' => 'ar_KW', 'KY' => 'en_KY', 'KZ' => 'ru_KZ', 'LA' => 'lo_LA',
        'LB' => 'ar_LB', 'LC' => 'en_LC', 'LI' => 'de_LI', 'LK' => 'si_LK', 'LR' => 'en_LR',
        'LS' => 'st_LS', 'LT' => 'lt_LT', 'LU' => 'fr_LU', 'LV' => 'lv_LV', 'LY' => 'ar_LY',
        'MA' => 'ar_MA', 'MC' => 'fr_MC', 'MD' => 'ro_MD', 'ME' => 'sr_Latn_ME', 'MF' => 'fr_MF',
        'MG' => 'mg_MG', 'MH' => 'mh_MH', 'MK' => 'mk_MK', 'ML' => 'bm_ML', 'MM' => 'my_MM',
        'MN' => 'mn_Cyrl_MN', 'MO' => 'zh_Hant_MO', 'MP' => 'en_MP', 'MQ' => 'fr_MQ', 'MR' => 'ar_MR',
        'MS' => 'en_MS', 'MT' => 'mt_MT', 'MU' => 'mfe_MU', 'MV' => 'dv_MV', 'MW' => 'ny_MW',
        'MX' => 'es_MX', 'MY' => 'ms_MY', 'MZ' => 'pt_MZ', 'NA' => 'kj_NA', 'NC' => 'fr_NC',
        'NE' => 'ha_Latn_NE', 'NF' => 'en_NF', 'NG' => 'en_NG', 'NI' => 'es_NI', 'NL' => 'nl_NL',
        'NO' => 'nb_NO', 'NP' => 'ne_NP', 'NR' => 'en_NR', 'NU' => 'niu_NU', 'NZ' => 'en_NZ',
        'OM' => 'ar_OM', 'PA' => 'es_PA', 'PE' => 'es_PE', 'PF' => 'fr_PF', 'PG' => 'tpi_PG',
        'PH' => 'fil_PH', 'PK' => 'ur_PK', 'PL' => 'pl_PL', 'PM' => 'fr_PM', 'PN' => 'en_PN',
        'PR' => 'es_PR', 'PS' => 'ar_PS', 'PT' => 'pt_PT', 'PW' => 'pau_PW', 'PY' => 'gn_PY',
        'QA' => 'ar_QA', 'RE' => 'fr_RE', 'RO' => 'ro_RO', 'RS' => 'sr_Cyrl_RS', 'RU' => 'ru_RU',
        'RW' => 'rw_RW', 'SA' => 'ar_SA', 'SB' => 'en_SB', 'SC' => 'crs_SC', 'SD' => 'ar_SD',
        'SE' => 'sv_SE', 'SG' => 'en_SG', 'SH' => 'en_SH', 'SI' => 'sl_SI', 'SJ' => 'nb_SJ',
        'SK' => 'sk_SK', 'SL' => 'kri_SL', 'SM' => 'it_SM', 'SN' => 'fr_SN', 'SO' => 'sw_SO',
        'SR' => 'srn_SR', 'ST' => 'pt_ST', 'SV' => 'es_SV', 'SY' => 'ar_SY', 'SZ' => 'en_SZ',
        'TC' => 'en_TC', 'TD' => 'fr_TD', 'TF' => 'und_TF', 'TG' => 'fr_TG', 'TH' => 'th_TH',
        'TJ' => 'tg_Cyrl_TJ', 'TK' => 'tkl_TK', 'TL' => 'pt_TL', 'TM' => 'tk_TM', 'TN' => 'ar_TN',
        'TO' => 'to_TO', 'TR' => 'tr_TR', 'TT' => 'en_TT', 'TV' => 'tvl_TV', 'TW' => 'zh_Hant_TW',
        'TZ' => 'sw_TZ', 'UA' => 'uk_UA', 'UG' => 'sw_UG', 'UM' => 'en_UM', 'US' => 'en_US',
        'UY' => 'es_UY', 'UZ' => 'uz_Cyrl_UZ', 'VA' => 'it_VA', 'VC' => 'en_VC', 'VE' => 'es_VE',
        'VG' => 'en_VG', 'VI' => 'en_VI', 'VU' => 'bi_VU', 'WF' => 'wls_WF', 'WS' => 'sm_WS',
        'YE' => 'ar_YE', 'YT' => 'swb_YT', 'ZA' => 'en_ZA', 'ZM' => 'en_ZM', 'ZW' => 'sn_ZW'
    );

    /**
     * Autosearch constants
     */
    const BROWSER     = 'browser';
    const ENVIRONMENT = 'environment';
    const ZFDEFAULT   = 'default';

    /**
     * Defines if old behaviour should be supported
     * Old behaviour throws notices and will be deleted in future releases
     *
     * @var boolean
     */
    public static $compatibilityMode = false;

    /**
     * Internal variable
     *
     * @var boolean
     */
    private static $_breakChain = false;

    /**
     * Actual set locale
     *
     * @var string Locale
     */
    protected $_locale;

    /**
     * Automatic detected locale
     *
     * @var string Locales
     */
    protected static $_auto;

    /**
     * Browser detected locale
     *
     * @var string Locales
     */
    protected static $_browser;

    /**
     * Environment detected locale
     *
     * @var string Locales
     */
    protected static $_environment;

    /**
     * Default locale
     *
     * @var string Locales
     */
    protected static $_default = array('en' => true);

    /**
     * Generates a locale object
     * If no locale is given a automatic search is done
     * Then the most probable locale will be automatically set
     * Search order is
     *  1. Given Locale
     *  2. HTTP Client
     *  3. Server Environment
     *  4. Framework Standard
     *
     * @param  string|Zend_Locale $locale (Optional) Locale for parsing input
     * @throws Zend_Locale_Exception When autodetection has been failed
     */
    public function __construct($locale = null)
    {
        $this->setLocale($locale);
    }

    /**
     * Serialization Interface
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return (string) $this->_locale;
    }

    /**
     * Returns a string representation of the object
     * Alias for toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Return the default locale
     *
     * @return array Returns an array of all locale string
     */
    public static function getDefault()
    {
        if ((self::$compatibilityMode === true) or (func_num_args() > 0)) {
            if (!self::$_breakChain) {
                self::$_breakChain = true;
                trigger_error('You are running Zend_Locale in compatibility mode... please migrate your scripts', E_USER_NOTICE);
                $params = func_get_args();
                $param = null;
                if (isset($params[0])) {
                    $param = $params[0];
                }
                return self::getOrder($param);
            }

            self::$_breakChain = false;
        }

        return self::$_default;
    }

    /**
     * Sets a new default locale which will be used when no locale can be detected
     * If provided you can set a quality between 0 and 1 (or 2 and 100)
     * which represents the percent of quality the browser
     * requested within HTTP
     *
     * @param  string|Zend_Locale $locale  Locale to set
     * @param  float              $quality The quality to set from 0 to 1
     * @throws Zend_Locale_Exception When a autolocale was given
     * @throws Zend_Locale_Exception When a unknown locale was given
     * @return void
     */
    public static function setDefault($locale, $quality = 1)
    {
        if (($locale === 'auto') or ($locale === 'root') or ($locale === 'default') or
            ($locale === 'environment') or ($locale === 'browser')) {
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception('Only full qualified locales can be used as default!');
        }

        if (($quality < 0.1) or ($quality > 100)) {
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception("Quality must be between 0.1 and 100");
        }

        if ($quality > 1) {
            $quality /= 100;
        }

        $locale = self::_prepareLocale($locale);
        if (isset(self::$_localeData[(string) $locale]) === true) {
            self::$_default = array((string) $locale => $quality);
        } else {
            $elocale = explode('_', (string) $locale);
            if (isset(self::$_localeData[$elocale[0]]) === true) {
                self::$_default = array($elocale[0] => $quality);
            } else {
                #require_once 'Zend/Locale/Exception.php';
                throw new Zend_Locale_Exception("Unknown locale '" . (string) $locale . "' can not be set as default!");
            }
        }

        self::$_auto = self::getBrowser() + self::getEnvironment() + self::getDefault();
    }

    /**
     * Expects the Systems standard locale
     *
     * For Windows:
     * f.e.: LC_COLLATE=C;LC_CTYPE=German_Austria.1252;LC_MONETARY=C
     * would be recognised as de_AT
     *
     * @return array
     */
    public static function getEnvironment()
    {
        if (self::$_environment !== null) {
            return self::$_environment;
        }

        #require_once 'Zend/Locale/Data/Translation.php';

        $language      = setlocale(LC_ALL, 0);
        $languages     = explode(';', $language);
        $languagearray = array();

        foreach ($languages as $locale) {
            if (strpos($locale, '=') !== false) {
                $language = substr($locale, strpos($locale, '='));
                $language = substr($language, 1);
            }

            if ($language !== 'C') {
                if (strpos($language, '.') !== false) {
                    $language = substr($language, 0, strpos($language, '.'));
                } else if (strpos($language, '@') !== false) {
                    $language = substr($language, 0, strpos($language, '@'));
                }

                $language = str_ireplace(
                    array_keys(Zend_Locale_Data_Translation::$languageTranslation),
                    array_values(Zend_Locale_Data_Translation::$languageTranslation),
                    (string) $language
                );

                $language = str_ireplace(
                    array_keys(Zend_Locale_Data_Translation::$regionTranslation),
                    array_values(Zend_Locale_Data_Translation::$regionTranslation),
                    $language
                );

                if (isset(self::$_localeData[$language]) === true) {
                    $languagearray[$language] = 1;
                    if (strpos($language, '_') !== false) {
                        $languagearray[substr($language, 0, strpos($language, '_'))] = 1;
                    }
                }
            }
        }

        self::$_environment = $languagearray;
        return $languagearray;
    }

    /**
     * Return an array of all accepted languages of the client
     * Expects RFC compilant Header !!
     *
     * The notation can be :
     * de,en-UK-US;q=0.5,fr-FR;q=0.2
     *
     * @return array - list of accepted languages including quality
     */
    public static function getBrowser()
    {
        if (self::$_browser !== null) {
            return self::$_browser;
        }

        $httplanguages = getenv('HTTP_ACCEPT_LANGUAGE');
        if (empty($httplanguages) && array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            $httplanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        $languages     = array();
        if (empty($httplanguages)) {
            return $languages;
        }

        $accepted = preg_split('/,\s*/', $httplanguages);

        foreach ($accepted as $accept) {
            $match  = null;
            $result = preg_match('/^([a-z]{1,8}(?:[-_][a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i',
                                 $accept, $match);

            if ($result < 1) {
                continue;
            }

            if (isset($match[2]) === true) {
                $quality = (float) $match[2];
            } else {
                $quality = 1.0;
            }

            $countrys = explode('-', $match[1]);
            $region   = array_shift($countrys);

            $country2 = explode('_', $region);
            $region   = array_shift($country2);

            foreach ($countrys as $country) {
                $languages[$region . '_' . strtoupper($country)] = $quality;
            }

            foreach ($country2 as $country) {
                $languages[$region . '_' . strtoupper($country)] = $quality;
            }

            if ((isset($languages[$region]) === false) || ($languages[$region] < $quality)) {
                $languages[$region] = $quality;
            }
        }

        self::$_browser = $languages;
        return $languages;
    }

    /**
     * Sets a new locale
     *
     * @param  string|Zend_Locale $locale (Optional) New locale to set
     * @return void
     */
    public function setLocale($locale = null)
    {
        $locale = self::_prepareLocale($locale);

        if (isset(self::$_localeData[(string) $locale]) === false) {
            $region = substr((string) $locale, 0, 3);
            if (isset($region[2]) === true) {
                if (($region[2] === '_') or ($region[2] === '-')) {
                    $region = substr($region, 0, 2);
                }
            }

            if (isset(self::$_localeData[(string) $region]) === true) {
                $this->_locale = $region;
            } else {
                $this->_locale = 'root';
            }
        } else {
            $this->_locale = $locale;
        }
    }

    /**
     * Returns the language part of the locale
     *
     * @return string
     */
    public function getLanguage()
    {
        $locale = explode('_', $this->_locale);
        return $locale[0];
    }

    /**
     * Returns the region part of the locale if available
     *
     * @return string|false - Regionstring
     */
    public function getRegion()
    {
        $locale = explode('_', $this->_locale);
        if (isset($locale[1]) === true) {
            return $locale[1];
        }

        return false;
    }

    /**
     * Return the accepted charset of the client
     *
     * @return string
     */
    public static function getHttpCharset()
    {
        $httpcharsets = getenv('HTTP_ACCEPT_CHARSET');

        $charsets = array();
        if ($httpcharsets === false) {
            return $charsets;
        }

        $accepted = preg_split('/,\s*/', $httpcharsets);
        foreach ($accepted as $accept) {
            if (empty($accept) === true) {
                continue;
            }

            if (strpos($accept, ';') !== false) {
                $quality        = (float) substr($accept, (strpos($accept, '=') + 1));
                $pos            = substr($accept, 0, strpos($accept, ';'));
                $charsets[$pos] = $quality;
            } else {
                $quality           = 1.0;
                $charsets[$accept] = $quality;
            }
        }

        return $charsets;
    }

    /**
     * Returns true if both locales are equal
     *
     * @param  Zend_Locale $object Locale to check for equality
     * @return boolean
     */
    public function equals(Zend_Locale $object)
    {
        if ($object->toString() === $this->toString()) {
            return true;
        }

        return false;
    }

    /**
     * Returns localized informations as array, supported are several
     * types of informations.
     * For detailed information about the types look into the documentation
     *
     * @param  string             $path   (Optional) Type of information to return
     * @param  string|Zend_Locale $locale (Optional) Locale|Language for which this informations should be returned
     * @param  string             $value  (Optional) Value for detail list
     * @return array Array with the wished information in the given language
     */
    public static function getTranslationList($path = null, $locale = null, $value = null)
    {
        #require_once 'Zend/Locale/Data.php';
        $locale = self::findLocale($locale);
        $result = Zend_Locale_Data::getList($locale, $path, $value);
        if (empty($result) === true) {
            return false;
        }

        return $result;
    }

    /**
     * Returns an array with the name of all languages translated to the given language
     *
     * @param  string|Zend_Locale $locale (Optional) Locale for language translation
     * @return array
     * @deprecated
     */
    public static function getLanguageTranslationList($locale = null)
    {
        trigger_error("The method getLanguageTranslationList is deprecated. Use getTranslationList('language', $locale) instead", E_USER_NOTICE);
        return self::getTranslationList('language', $locale);
    }

    /**
     * Returns an array with the name of all scripts translated to the given language
     *
     * @param  string|Zend_Locale $locale (Optional) Locale for script translation
     * @return array
     * @deprecated
     */
    public static function getScriptTranslationList($locale = null)
    {
        trigger_error("The method getScriptTranslationList is deprecated. Use getTranslationList('script', $locale) instead", E_USER_NOTICE);
        return self::getTranslationList('script', $locale);
    }

    /**
     * Returns an array with the name of all countries translated to the given language
     *
     * @param  string|Zend_Locale $locale (Optional) Locale for country translation
     * @return array
     * @deprecated
     */
    public static function getCountryTranslationList($locale = null)
    {
        trigger_error("The method getCountryTranslationList is deprecated. Use getTranslationList('territory', $locale, 2) instead", E_USER_NOTICE);
        return self::getTranslationList('territory', $locale, 2);
    }

    /**
     * Returns an array with the name of all territories translated to the given language
     * All territories contains other countries.
     *
     * @param  string|Zend_Locale $locale (Optional) Locale for territory translation
     * @return array
     * @deprecated
     */
    public static function getTerritoryTranslationList($locale = null)
    {
        trigger_error("The method getTerritoryTranslationList is deprecated. Use getTranslationList('territory', $locale, 1) instead", E_USER_NOTICE);
        return self::getTranslationList('territory', $locale, 1);
    }

    /**
     * Returns a localized information string, supported are several types of informations.
     * For detailed information about the types look into the documentation
     *
     * @param  string             $value  Name to get detailed information about
     * @param  string             $path   (Optional) Type of information to return
     * @param  string|Zend_Locale $locale (Optional) Locale|Language for which this informations should be returned
     * @return string|false The wished information in the given language
     */
    public static function getTranslation($value = null, $path = null, $locale = null)
    {
        #require_once 'Zend/Locale/Data.php';
        $locale = self::findLocale($locale);
        $result = Zend_Locale_Data::getContent($locale, $path, $value);
        if (empty($result) === true) {
            return false;
        }

        return $result;
    }

    /**
     * Returns the localized language name
     *
     * @param  string $value  Name to get detailed information about
     * @param  string $locale (Optional) Locale for language translation
     * @return array
     * @deprecated
     */
    public static function getLanguageTranslation($value, $locale = null)
    {
        trigger_error("The method getLanguageTranslation is deprecated. Use getTranslation($value, 'language', $locale) instead", E_USER_NOTICE);
        return self::getTranslation($value, 'language', $locale);
    }

    /**
     * Returns the localized script name
     *
     * @param  string $value  Name to get detailed information about
     * @param  string $locale (Optional) locale for script translation
     * @return array
     * @deprecated
     */
    public static function getScriptTranslation($value, $locale = null)
    {
        trigger_error("The method getScriptTranslation is deprecated. Use getTranslation($value, 'script', $locale) instead", E_USER_NOTICE);
        return self::getTranslation($value, 'script', $locale);
    }

    /**
     * Returns the localized country name
     *
     * @param  string             $value  Name to get detailed information about
     * @param  string|Zend_Locale $locale (Optional) Locale for country translation
     * @return array
     * @deprecated
     */
    public static function getCountryTranslation($value, $locale = null)
    {
        trigger_error("The method getCountryTranslation is deprecated. Use getTranslation($value, 'country', $locale) instead", E_USER_NOTICE);
        return self::getTranslation($value, 'country', $locale);
    }

    /**
     * Returns the localized territory name
     * All territories contains other countries.
     *
     * @param  string             $value  Name to get detailed information about
     * @param  string|Zend_Locale $locale (Optional) Locale for territory translation
     * @return array
     * @deprecated
     */
    public static function getTerritoryTranslation($value, $locale = null)
    {
        trigger_error("The method getTerritoryTranslation is deprecated. Use getTranslation($value, 'territory', $locale) instead", E_USER_NOTICE);
        return self::getTranslation($value, 'territory', $locale);
    }

    /**
     * Returns an array with translated yes strings
     *
     * @param  string|Zend_Locale $locale (Optional) Locale for language translation (defaults to $this locale)
     * @return array
     */
    public static function getQuestion($locale = null)
    {
        #require_once 'Zend/Locale/Data.php';
        $locale            = self::findLocale($locale);
        $quest             = Zend_Locale_Data::getList($locale, 'question');
        $yes               = explode(':', $quest['yes']);
        $no                = explode(':', $quest['no']);
        $quest['yes']      = $yes[0];
        $quest['yesarray'] = $yes;
        $quest['no']       = $no[0];
        $quest['noarray']  = $no;
        $quest['yesexpr']  = self::_prepareQuestionString($yes);
        $quest['noexpr']   = self::_prepareQuestionString($no);

        return $quest;
    }

    /**
     * Internal function for preparing the returned question regex string
     *
     * @param  string $input Regex to parse
     * @return string
     */
    private static function _prepareQuestionString($input)
    {
        $regex = '';
        if (is_array($input) === true) {
            $regex = '^';
            $start = true;
            foreach ($input as $row) {
                if ($start === false) {
                    $regex .= '|';
                }

                $start  = false;
                $regex .= '(';
                $one    = null;
                if (strlen($row) > 2) {
                    $one = true;
                }

                foreach (str_split($row, 1) as $char) {
                    $regex .= '[' . $char;
                    $regex .= strtoupper($char) . ']';
                    if ($one === true) {
                        $one    = false;
                        $regex .= '(';
                    }
                }

                if ($one === false) {
                    $regex .= ')';
                }

                $regex .= '?)';
            }
        }

        return $regex;
    }

    /**
     * Checks if a locale identifier is a real locale or not
     * Examples:
     * "en_XX" refers to "en", which returns true
     * "XX_yy" refers to "root", which returns false
     *
     * @param  string|Zend_Locale $locale     Locale to check for
     * @param  boolean            $strict     (Optional) If true, no rerouting will be done when checking
     * @param  boolean            $compatible (DEPRECATED) Only for internal usage, brakes compatibility mode
     * @return boolean If the locale is known dependend on the settings
     */
    public static function isLocale($locale, $strict = false, $compatible = true)
    {
        if (($locale instanceof Zend_Locale)
            || (is_string($locale) && array_key_exists($locale, self::$_localeData))
        ) {
            return true;
        }

        if (($locale === null) || (!is_string($locale) and !is_array($locale))) {
            return false;
        }

        try {
            $locale = self::_prepareLocale($locale, $strict);
        } catch (Zend_Locale_Exception $e) {
            return false;
        }

        if (($compatible === true) and (self::$compatibilityMode === true)) {
            trigger_error('You are running Zend_Locale in compatibility mode... please migrate your scripts', E_USER_NOTICE);
            if (isset(self::$_localeData[$locale]) === true) {
                return $locale;
            } else if (!$strict) {
                $locale = explode('_', $locale);
                if (isset(self::$_localeData[$locale[0]]) === true) {
                    return $locale[0];
                }
            }
        } else {
            if (isset(self::$_localeData[$locale]) === true) {
                return true;
            } else if (!$strict) {
                $locale = explode('_', $locale);
                if (isset(self::$_localeData[$locale[0]]) === true) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Finds the proper locale based on the input
     * Checks if it exists, degrades it when necessary
     * Detects registry locale and when all fails tries to detect a automatic locale
     * Returns the found locale as string
     *
     * @param string $locale
     * @throws Zend_Locale_Exception When the given locale is no locale or the autodetection fails
     * @return string
     */
    public static function findLocale($locale = null)
    {
        if ($locale === null) {
            #require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Locale')) {
                $locale = Zend_Registry::get('Zend_Locale');
            }
        }

        if ($locale === null) {
            $locale = new Zend_Locale();
        }

        if (!Zend_Locale::isLocale($locale, true, false)) {
            if (!Zend_Locale::isLocale($locale, false, false)) {
                $locale = Zend_Locale::getLocaleToTerritory($locale);

                if (empty($locale)) {
                    #require_once 'Zend/Locale/Exception.php';
                    throw new Zend_Locale_Exception("The locale '$locale' is no known locale");
                }
            } else {
                $locale = new Zend_Locale($locale);
            }
        }

        $locale = self::_prepareLocale($locale);
        return $locale;
    }

    /**
     * Returns the expected locale for a given territory
     *
     * @param string $territory Territory for which the locale is being searched
     * @return string|null Locale string or null when no locale has been found
     */
    public static function getLocaleToTerritory($territory)
    {
        $territory = strtoupper($territory);
        if (array_key_exists($territory, self::$_territoryData)) {
            return self::$_territoryData[$territory];
        }

        return null;
    }

    /**
     * Returns a list of all known locales where the locale is the key
     * Only real locales are returned, the internal locales 'root', 'auto', 'browser'
     * and 'environment' are suppressed
     *
     * @return array List of all Locales
     */
    public static function getLocaleList()
    {
        $list = self::$_localeData;
        unset($list['root']);
        unset($list['auto']);
        unset($list['browser']);
        unset($list['environment']);
        return $list;
    }

    /**
     * Returns the set cache
     *
     * @return Zend_Cache_Core The set cache
     */
    public static function getCache()
    {
        #require_once 'Zend/Locale/Data.php';
        return Zend_Locale_Data::getCache();
    }

    /**
     * Sets a cache
     *
     * @param  Zend_Cache_Core $cache Cache to set
     * @return void
     */
    public static function setCache(Zend_Cache_Core $cache)
    {
        #require_once 'Zend/Locale/Data.php';
        Zend_Locale_Data::setCache($cache);
    }

    /**
     * Returns true when a cache is set
     *
     * @return boolean
     */
    public static function hasCache()
    {
        #require_once 'Zend/Locale/Data.php';
        return Zend_Locale_Data::hasCache();
    }

    /**
     * Removes any set cache
     *
     * @return void
     */
    public static function removeCache()
    {
        #require_once 'Zend/Locale/Data.php';
        Zend_Locale_Data::removeCache();
    }

    /**
     * Clears all set cache data
     *
     * @param string $tag Tag to clear when the default tag name is not used
     * @return void
     */
    public static function clearCache($tag = null)
    {
        #require_once 'Zend/Locale/Data.php';
        Zend_Locale_Data::clearCache($tag);
    }

    /**
     * Disables the set cache
     *
     * @param  boolean $flag True disables any set cache, default is false
     * @return void
     */
    public static function disableCache($flag)
    {
        #require_once 'Zend/Locale/Data.php';
        Zend_Locale_Data::disableCache($flag);
    }

    /**
     * Internal function, returns a single locale on detection
     *
     * @param  string|Zend_Locale $locale (Optional) Locale to work on
     * @param  boolean            $strict (Optional) Strict preparation
     * @throws Zend_Locale_Exception When no locale is set which is only possible when the class was wrong extended
     * @return string
     */
    private static function _prepareLocale($locale, $strict = false)
    {
        if ($locale instanceof Zend_Locale) {
            $locale = $locale->toString();
        }

        if (is_array($locale)) {
            return '';
        }

        if (empty(self::$_auto) === true) {
            self::$_browser     = self::getBrowser();
            self::$_environment = self::getEnvironment();
            self::$_breakChain  = true;
            self::$_auto        = self::getBrowser() + self::getEnvironment() + self::getDefault();
        }

        if (!$strict) {
            if ($locale === 'browser') {
                $locale = self::$_browser;
            }

            if ($locale === 'environment') {
                $locale = self::$_environment;
            }

            if ($locale === 'default') {
                $locale = self::$_default;
            }

            if (($locale === 'auto') or ($locale === null)) {
                $locale = self::$_auto;
            }

            if (is_array($locale) === true) {
                $locale = key($locale);
            }
        }

        // This can only happen when someone extends Zend_Locale and erases the default
        if ($locale === null) {
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception('Autodetection of Locale has been failed!');
        }

        if (strpos($locale, '-') !== false) {
            $locale = strtr($locale, '-', '_');
        }

        $parts = explode('_', $locale);
        if (!isset(self::$_localeData[$parts[0]])) {
            if ((count($parts) == 1) && array_key_exists($parts[0], self::$_territoryData)) {
                return self::$_territoryData[$parts[0]];
            }

            return '';
        }

        foreach($parts as $key => $value) {
            if ((strlen($value) < 2) || (strlen($value) > 3)) {
                unset($parts[$key]);
            }
        }

        $locale = implode('_', $parts);
        return (string) $locale;
    }

    /**
     * Search the locale automatically and return all used locales
     * ordered by quality
     *
     * Standard Searchorder is Browser, Environment, Default
     *
     * @param  string  $searchorder (Optional) Searchorder
     * @return array Returns an array of all detected locales
     */
    public static function getOrder($order = null)
    {
        switch ($order) {
            case self::ENVIRONMENT:
                self::$_breakChain = true;
                $languages         = self::getEnvironment() + self::getBrowser() + self::getDefault();
                break;

            case self::ZFDEFAULT:
                self::$_breakChain = true;
                $languages         = self::getDefault() + self::getEnvironment() + self::getBrowser();
                break;

            default:
                self::$_breakChain = true;
                $languages         = self::getBrowser() + self::getEnvironment() + self::getDefault();
                break;
        }

        return $languages;
    }
}
