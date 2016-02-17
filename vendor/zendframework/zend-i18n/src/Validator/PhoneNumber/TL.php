<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '670',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-489]\\d{6}|7\\d{6,7}$/',
            'fixed' => '/^(?:2[1-5]|3[1-9]|4[1-4])\\d{5}$/',
            'mobile' => '/^7[78]\\d{6}$/',
            'tollfree' => '/^80\\d{5}$/',
            'premium' => '/^90\\d{5}$/',
            'personal' => '/^70\\d{5}$/',
            'shortcode' => '/^1(?:0[02]|2[0138]|72|9[07])$/',
            'emergency' => '/^11[25]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,8}$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{7}$/',
            'premium' => '/^\\d{7}$/',
            'personal' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
