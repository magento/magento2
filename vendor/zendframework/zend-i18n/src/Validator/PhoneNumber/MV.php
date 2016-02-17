<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '960',
    'patterns' => array(
        'national' => array(
            'general' => '/^[3467]\\d{6}|9(?:00\\d{7}|\\d{6})$/',
            'fixed' => '/^(?:3(?:0[01]|3[0-59])|6(?:[567][02468]|8[024689]|90))\\d{4}$/',
            'mobile' => '/^(?:46[46]|7[3-9]\\d|9[6-9]\\d)\\d{4}$/',
            'pager' => '/^781\\d{4}$/',
            'premium' => '/^900\\d{7}$/',
            'shortcode' => '/^1(?:[19]0|23)$/',
            'emergency' => '/^1(?:02|19)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,10}$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{7}$/',
            'pager' => '/^\\d{7}$/',
            'premium' => '/^\\d{10}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
