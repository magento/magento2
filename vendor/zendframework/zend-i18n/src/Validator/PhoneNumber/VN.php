<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '84',
    'patterns' => array(
        'national' => array(
            'general' => '/^[17]\\d{6,9}|[2-69]\\d{7,9}|8\\d{6,8}$/',
            'fixed' => '/^(?:2(?:[025-79]|1[0189]|[348][01])|3(?:[0136-9]|[25][01])|4\\d|5(?:[01][01]|[2-9])|6(?:[0-46-8]|5[01])|7(?:[02-79]|[18][01])|8[1-9])\\d{7}$/',
            'mobile' => '/^(?:9\\d|1(?:2\\d|6[2-9]|8[68]|99))\\d{7}$/',
            'tollfree' => '/^1800\\d{4,6}$/',
            'premium' => '/^1900\\d{4,6}$/',
            'uan' => '/^[17]99\\d{4}|69\\d{5,6}|80\\d{5}$/',
            'emergency' => '/^11[345]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,10}$/',
            'fixed' => '/^\\d{9,10}$/',
            'mobile' => '/^\\d{9,10}$/',
            'tollfree' => '/^\\d{8,10}$/',
            'premium' => '/^\\d{8,10}$/',
            'uan' => '/^\\d{7,8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
