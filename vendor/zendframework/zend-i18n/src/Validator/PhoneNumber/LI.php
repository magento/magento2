<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '423',
    'patterns' => array(
        'national' => array(
            'general' => '/^6\\d{8}|[23789]\\d{6}$/',
            'fixed' => '/^(?:2(?:01|1[27]|3\\d|6[02-578]|96)|3(?:7[0135-7]|8[048]|9[0269]))\\d{4}$/',
            'mobile' => '/^6(?:51[01]|6(?:[01][0-4]|2[016-9]|88)|710)\\d{5}|7(?:36|4[25]|56|[7-9]\\d)\\d{4}$/',
            'tollfree' => '/^80(?:0(?:2[238]|79)|9\\d{2})\\d{2}$/',
            'premium' => '/^90(?:0(?:2[278]|79)|1(?:23|3[012])|6(?:4\\d|6[0126]))\\d{2}$/',
            'uan' => '/^87(?:0[128]|7[0-4])\\d{3}$/',
            'voicemail' => '/^697(?:[35]6|4[25]|[7-9]\\d)\\d{4}$/',
            'personal' => '/^701\\d{4}$/',
            'shortcode' => '/^1(?:145|4(?:[0357]|14)|50\\d{4}|6(?:00|[1-4])|75|8(?:1[128]|7))$/',
            'emergency' => '/^1(?:1[278]|44)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'fixed' => '/^\\d{7}$/',
            'tollfree' => '/^\\d{7}$/',
            'premium' => '/^\\d{7}$/',
            'uan' => '/^\\d{7}$/',
            'voicemail' => '/^\\d{9}$/',
            'personal' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
