<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '262',
    'patterns' => array(
        'national' => array(
            'general' => '/^[268]\\d{8}$/',
            'fixed' => '/^262\\d{6}$/',
            'mobile' => '/^6(?:9[23]|47)\\d{6}$/',
            'tollfree' => '/^80\\d{7}$/',
            'premium' => '/^89[1-37-9]\\d{6}$/',
            'shared' => '/^8(?:1[019]|2[0156]|84|90)\\d{6}$/',
            'emergency' => '/^1(?:12|[578])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
