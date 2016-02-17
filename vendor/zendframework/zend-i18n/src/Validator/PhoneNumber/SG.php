<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '65',
    'patterns' => array(
        'national' => array(
            'general' => '/^[36]\\d{7}|[17-9]\\d{7,10}$/',
            'fixed' => '/^6[1-9]\\d{6}$/',
            'mobile' => '/^(?:8[1-7]|9[0-8])\\d{6}$/',
            'tollfree' => '/^1?800\\d{7}$/',
            'premium' => '/^1900\\d{7}$/',
            'voip' => '/^3[12]\\d{6}$/',
            'uan' => '/^7000\\d{7}$/',
            'shortcode' => '/^1(?:[0136]\\d{2}|[89](?:[1-9]\\d|0[1-9])|[57]\\d{2,3})|99[0246-8]$/',
            'emergency' => '/^99[359]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8,11}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{10,11}$/',
            'premium' => '/^\\d{11}$/',
            'voip' => '/^\\d{8}$/',
            'uan' => '/^\\d{11}$/',
            'shortcode' => '/^\\d{3,5}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
