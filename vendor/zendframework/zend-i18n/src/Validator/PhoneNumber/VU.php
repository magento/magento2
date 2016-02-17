<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '678',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-57-9]\\d{4,6}$/',
            'fixed' => '/^(?:2[2-9]\\d|3(?:[5-7]\\d|8[0-8])|48[4-9]|88\\d)\\d{2}$/',
            'mobile' => '/^(?:5(?:7[2-5]|[3-69]\\d)|7[013-7]\\d)\\d{4}$/',
            'uan' => '/^3[03]\\d{3}|900\\d{4}$/',
            'emergency' => '/^112$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,7}$/',
            'fixed' => '/^\\d{5}$/',
            'mobile' => '/^\\d{7}$/',
            'uan' => '/^\\d{5,7}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
