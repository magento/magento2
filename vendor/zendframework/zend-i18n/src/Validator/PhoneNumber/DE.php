<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '49',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-35-9]\\d{3,14}|4(?:[0-8]\\d{4,12}|9(?:[0-37]\\d|4(?:[1-35-8]|4\\d?)|5\\d{1,2}|6[1-8]\\d?)\\d{2,7})$/',
            'fixed' => '/^[246]\\d{5,13}|3(?:[03-9]\\d{4,13}|2\\d{9})|5(?:0[2-8]|[1256]\\d|[38][0-8]|4\\d{0,2}|[79][0-7])\\d{3,11}|7(?:0[2-8]|[1-9]\\d)\\d{3,10}|8(?:0[2-9]|[1-9]\\d)\\d{3,10}|9(?:0[6-9]|[1-9]\\d)\\d{3,10}$/',
            'mobile' => '/^1(?:5[0-2579]\\d{8}|6[023]\\d{7,8}|7(?:[0-57-9]\\d?|6\\d)\\d{7})$/',
            'pager' => '/^16(?:4\\d{1,10}|[89]\\d{1,11})$/',
            'tollfree' => '/^800\\d{7,10}$/',
            'premium' => '/^900(?:[135]\\d{6}|9\\d{7})$/',
            'shared' => '/^180\\d{5,11}$/',
            'personal' => '/^700\\d{8}$/',
            'uan' => '/^18(?:1\\d{5,11}|[2-9]\\d{8})$/',
            'voicemail' => '/^17799\\d{7,8}$/',
            'shortcode' => '/^115$/',
            'emergency' => '/^11[02]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{2,15}$/',
            'mobile' => '/^\\d{10,11}$/',
            'pager' => '/^\\d{4,14}$/',
            'tollfree' => '/^\\d{10,13}$/',
            'premium' => '/^\\d{10,11}$/',
            'shared' => '/^\\d{8,14}$/',
            'personal' => '/^\\d{11}$/',
            'uan' => '/^\\d{8,14}$/',
            'voicemail' => '/^\\d{12,13}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
