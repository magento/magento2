<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '261',
    'patterns' => array(
        'national' => array(
            'general' => '/^[23]\\d{8}$/',
            'fixed' => '/^2(?:0(?:(?:2\\d|4[47]|5[3467]|6[279]|8[268]|9[245])\\d|7(?:2[29]|[35]\\d))|210\\d)\\d{4}$/',
            'mobile' => '/^3[02-4]\\d{7}$/',
            'emergency' => '/^11?[78]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
