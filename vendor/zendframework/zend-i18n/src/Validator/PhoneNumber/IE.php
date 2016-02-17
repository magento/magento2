<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '353',
    'patterns' => array(
        'national' => array(
            'general' => '/^[124-9]\\d{6,9}$/',
            'fixed' => '/^1\\d{7,8}|2(?:1\\d{6,7}|3\\d{7}|[24-9]\\d{5})|4(?:0[24]\\d{5}|[1-469]\\d{7}|5\\d{6}|7\\d{5}|8[0-46-9]\\d{7})|5(?:0[45]\\d{5}|1\\d{6}|[23679]\\d{7}|8\\d{5})|6(?:1\\d{6}|[237-9]\\d{5}|[4-6]\\d{7})|7[14]\\d{7}|9(?:1\\d{6}|[04]\\d{7}|[35-9]\\d{5})$/',
            'mobile' => '/^8(?:22\\d{6}|[35-9]\\d{7})$/',
            'tollfree' => '/^1800\\d{6}$/',
            'premium' => '/^15(?:1[2-8]|[2-8]0|9[089])\\d{6}$/',
            'shared' => '/^18[59]0\\d{6}$/',
            'personal' => '/^700\\d{6}$/',
            'voip' => '/^76\\d{7}$/',
            'uan' => '/^818\\d{6}$/',
            'voicemail' => '/^8[35-9]\\d{8}$/',
            'emergency' => '/^112|999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,10}$/',
            'fixed' => '/^\\d{5,10}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'shared' => '/^\\d{10}$/',
            'personal' => '/^\\d{9}$/',
            'voip' => '/^\\d{9}$/',
            'uan' => '/^\\d{9}$/',
            'voicemail' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
