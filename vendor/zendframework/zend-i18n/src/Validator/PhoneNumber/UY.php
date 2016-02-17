<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '598',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2489]\\d{6,7}$/',
            'fixed' => '/^2\\d{7}|4[2-7]\\d{6}$/',
            'mobile' => '/^9[13-9]\\d{6}$/',
            'tollfree' => '/^80[05]\\d{4}$/',
            'premium' => '/^90[0-8]\\d{4}$/',
            'shortcode' => '/^1(?:0[4-9]|1[2368]|2[0-3568])$/',
            'emergency' => '/^128|911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{7}$/',
            'premium' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
