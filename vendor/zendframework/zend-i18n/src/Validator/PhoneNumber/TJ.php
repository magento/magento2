<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '992',
    'patterns' => array(
        'national' => array(
            'general' => '/^[3-59]\\d{8}$/',
            'fixed' => '/^(?:3(?:1[3-5]|2[245]|3[12]|4[24-7]|5[25]|72)|4(?:46|74|87))\\d{6}$/',
            'mobile' => '/^(?:505|9[0-35-9]\\d)\\d{6}$/',
            'emergency' => '/^1(?:0[1-3]|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{3,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
