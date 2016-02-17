<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '230',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-9]\\d{6}$/',
            'fixed' => '/^(?:2(?:[034789]\\d|1[0-7]|6[1-69])|4(?:[013-8]\\d|2[4-7])|[56]\\d{2}|8(?:14|3[129]))\\d{4}$/',
            'mobile' => '/^(?:25\\d|4(?:2[12389]|9\\d)|7\\d{2}|8(?:20|7[15-8])|9[1-8]\\d)\\d{4}$/',
            'pager' => '/^2(?:1[89]|2\\d)\\d{4}$/',
            'tollfree' => '/^80[012]\\d{4}$/',
            'premium' => '/^30\\d{5}$/',
            'voip' => '/^3(?:20|9\\d)\\d{4}$/',
            'shortcode' => '/^1(?:1[0-36-9]|[02-9]\\d|\\d{3,4})|8\\d{3}$/',
            'emergency' => '/^11[45]|99\\d$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3,5}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
