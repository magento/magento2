<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '57',
    'patterns' => array(
        'national' => array(
            'general' => '/^(?:[13]\\d{0,3}|[24-8])\\d{7}$/',
            'fixed' => '/^[124-8][2-9]\\d{6}$/',
            'mobile' => '/^3(?:0[0-24]|1\\d|2[01])\\d{7}$/',
            'tollfree' => '/^1800\\d{7}$/',
            'premium' => '/^19(?:0[01]|4[78])\\d{7}$/',
            'emergency' => '/^1(?:1[29]|23|32|56)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,11}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{11}$/',
            'premium' => '/^\\d{11}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
