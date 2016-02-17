<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '212',
    'patterns' => array(
        'national' => array(
            'general' => '/^[5689]\\d{8}$/',
            'fixed' => '/^5(?:2(?:(?:[015-7]\\d|2[2-9]|3[2-57]|4[2-8]|8[235-7])\\d|9(?:0\\d|[89]0))|3(?:(?:[0-4]\\d|[57][2-9]|6[235-8]|9[3-9])\\d|8(?:0\\d|[89]0)))\\d{4}$/',
            'mobile' => '/^6(?:0[0-6]|[14-7]\\d|2[2-46-9]|3[03-8]|8[01]|99)\\d{6}$/',
            'tollfree' => '/^80\\d{7}$/',
            'premium' => '/^89\\d{7}$/',
            'emergency' => '/^1(?:[59]|77)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
