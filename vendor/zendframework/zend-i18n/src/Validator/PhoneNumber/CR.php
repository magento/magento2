<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '506',
    'patterns' => array(
        'national' => array(
            'general' => '/^[24-9]\\d{7,9}$/',
            'fixed' => '/^2[24-7]\\d{6}$/',
            'mobile' => '/^5(?:0[0-4]|7[01])\\d{5}|[67][0-2]\\d{6}|8[3-9]\\d{6}$/',
            'tollfree' => '/^800\\d{7}$/',
            'premium' => '/^90[059]\\d{7}$/',
            'voip' => '/^210[0-6]\\d{4}|4(?:0(?:[04]0\\d{4}|10[0-3]\\d{3}|2(?:00\\d|900)\\d{2}|3[01]\\d{4}|5\\d{5}|70[01]\\d{3})|1[01]\\d{5}|400\\d{4})|5100\\d{4}$/',
            'shortcode' => '/^1(?:0(?:00|15|2[2-4679])|1(?:1[0-35-9]|37|[46]6|75|8[79]|9[0-379])|2(?:00|[12]2|34|55)|333|400|5(?:15|5[15])|693|7(?:00|1[789]|2[02]|[67]7))$/',
            'emergency' => '/^112|911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8,10}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'voip' => '/^\\d{8}$/',
            'shortcode' => '/^\\d{4}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
