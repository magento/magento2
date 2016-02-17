<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '66',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-9]\\d{7,8}|1\\d{3}(?:\\d{6})?$/',
            'fixed' => '/^(?:2[1-9]|3[2-9]|4[2-5]|5[2-6]|7[3-7])\\d{6}$/',
            'mobile' => '/^[89]\\d{8}$/',
            'tollfree' => '/^1800\\d{6}$/',
            'premium' => '/^1900\\d{6}$/',
            'voip' => '/^60\\d{7}$/',
            'uan' => '/^1\\d{3}$/',
            'emergency' => '/^1(?:669|9[19])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{4}|\\d{8,10}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'voip' => '/^\\d{9}$/',
            'uan' => '/^\\d{4}$/',
            'emergency' => '/^\\d{3,4}$/',
        ),
    ),
);
