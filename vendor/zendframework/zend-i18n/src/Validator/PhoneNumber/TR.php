<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '90',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-589]\\d{9}|444\\d{4}$/',
            'fixed' => '/^(?:2(?:[13][26]|[28][2468]|[45][268]|[67][246])|3(?:[13][28]|[24-6][2468]|[78][02468]|92)|4(?:[16][246]|[23578][2468]|4[26]))\\d{7}$/',
            'mobile' => '/^5(?:0[1-7]|22|[34]\\d|5[1-59]|9[246])\\d{7}$/',
            'pager' => '/^512\\d{7}$/',
            'tollfree' => '/^800\\d{7}$/',
            'premium' => '/^900\\d{7}$/',
            'uan' => '/^444\\d{4}|850\\d{7}$/',
            'emergency' => '/^1(?:1[02]|55)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,10}$/',
            'fixed' => '/^\\d{10}$/',
            'mobile' => '/^\\d{10}$/',
            'pager' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'uan' => '/^\\d{7,10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
