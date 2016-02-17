<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '44',
    'patterns' => array(
        'national' => array(
            'general' => '/^[135789]\\d{6,9}$/',
            'fixed' => '/^1534\\d{6}$/',
            'mobile' => '/^7(?:509|7(?:00|97)|829|937)\\d{6}$/',
            'pager' => '/^76(?:0[012]|2[356]|4[0134]|5[49]|6[0-369]|77|81|9[39])\\d{6}$/',
            'tollfree' => '/^80(?:07(?:35|81)|8901)\\d{4}$/',
            'premium' => '/^(?:871206|90(?:066[59]|1810|71(?:07|55)))\\d{4}$/',
            'shared' => '/^8(?:4(?:4(?:4(?:05|42|69)|703)|5(?:041|800))|70002)\\d{4}$/',
            'personal' => '/^701511\\d{4}$/',
            'voip' => '/^56\\d{8}$/',
            'uan' => '/^3(?:0(?:07(?:35|81)|8901)|3\\d{4}|4(?:4(?:4(?:05|42|69)|703)|5(?:041|800))|7(?:0002|1206))\\d{4}|55\\d{8}$/',
            'shortcode' => '/^1(?:00|18\\d{3}|23|4(?:[14]|28|7\\d)|5\\d|7(?:0[12]|[128]|35?)|808|9[135])|23[234]$/',
            'emergency' => '/^112|999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,10}$/',
            'mobile' => '/^\\d{10}$/',
            'pager' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'shared' => '/^\\d{10}$/',
            'personal' => '/^\\d{10}$/',
            'voip' => '/^\\d{10}$/',
            'uan' => '/^\\d{10}$/',
            'shortcode' => '/^\\d{3,6}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
