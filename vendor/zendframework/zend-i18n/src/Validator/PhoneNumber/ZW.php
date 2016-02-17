<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '263',
    'patterns' => array(
        'national' => array(
            'general' => '/^2(?:[012457-9]\\d{3,8}|6\\d{3,6})|[13-79]\\d{4,8}|86\\d{8}$/',
            'fixed' => '/^(?:1[3-9]|2(?:0[45]|[16]|2[28]|[49]8?|58[23]|7[246]|8[1346-9])|3(?:08?|17?|3[78]|[2456]|7[1569]|8[379])|5(?:[07-9]|1[78]|483|5(?:7?|8))|6(?:0|28|37?|[45][68][78]|98?)|848)\\d{3,6}|(?:2(?:27|5|7[135789]|8[25])|3[39]|5[1-46]|6[126-8])\\d{4,6}|2(?:0|70)\\d{5,6}|(?:4\\d|9[2-8])\\d{4,7}$/',
            'mobile' => '/^7[137]\\d{7}|86(?:22|44)\\d{6}$/',
            'voip' => '/^86(?:1[12]|30|8[367]|99)\\d{6}$/',
            'emergency' => '/^(?:112|99[3459])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{3,10}$/',
            'mobile' => '/^\\d{9,10}$/',
            'voip' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
