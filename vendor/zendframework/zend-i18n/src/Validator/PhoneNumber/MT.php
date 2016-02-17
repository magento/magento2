<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '356',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2579]\\d{7}$/',
            'fixed' => '/^2(?:0(?:1[0-6]|[69]\\d)|[1-357]\\d{2})\\d{4}$/',
            'mobile' => '/^(?:7(?:210|[79]\\d{2})|9(?:2[13]\\d|696|8(?:1[1-3]|89|97)|9\\d{2}))\\d{4}$/',
            'pager' => '/^7117\\d{4}$/',
            'premium' => '/^50(?:0(?:3[1679]|4\\d)|[169]\\d{2}|7[06]\\d)\\d{3}$/',
            'emergency' => '/^112$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
