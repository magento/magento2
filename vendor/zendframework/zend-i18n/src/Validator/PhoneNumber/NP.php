<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '977',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-8]\\d{7}|9(?:[1-69]\\d{6}|7[2-6]\\d{5,7}|8\\d{8})$/',
            'fixed' => '/^(?:1[0124-6]|2[13-79]|3[135-8]|4[146-9]|5[135-7]|6[13-9]|7[15-9]|8[1-46-9]|9[1-79])\\d{6}$/',
            'mobile' => '/^9(?:7[45]|8[0145])\\d{7}$/',
            'emergency' => '/^1(?:0[0-3]|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,10}$/',
            'fixed' => '/^\\d{6,8}$/',
            'mobile' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
