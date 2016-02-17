<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '596',
    'patterns' => array(
        'national' => array(
            'general' => '/^[56]\\d{8}$/',
            'fixed' => '/^596(?:0[2-5]|[12]0|3[05-9]|4[024-8]|[5-7]\\d|89|9[4-8])\\d{4}$/',
            'mobile' => '/^696(?:[0-479]\\d|5[01]|8[0-689])\\d{4}$/',
            'emergency' => '/^1(?:12|[578])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
