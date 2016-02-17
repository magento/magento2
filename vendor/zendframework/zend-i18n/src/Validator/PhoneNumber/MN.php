<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '976',
    'patterns' => array(
        'national' => array(
            'general' => '/^[12]\\d{7,9}|[57-9]\\d{7}$/',
            'fixed' => '/^[12](?:1\\d|2(?:[1-3]\\d?|7\\d)|3[2-8]\\d{1,2}|4[2-68]\\d{1,2}|5[1-4689]\\d{1,2})\\d{5}|5[0568]\\d{6}$/',
            'mobile' => '/^(?:8[89]|9[013-9])\\d{6}$/',
            'voip' => '/^7[05-8]\\d{6}$/',
            'emergency' => '/^10[0-3]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,10}$/',
            'mobile' => '/^\\d{8}$/',
            'voip' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
