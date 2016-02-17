<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '387',
    'patterns' => array(
        'national' => array(
            'general' => '/^[3-9]\\d{7,8}$/',
            'fixed' => '/^(?:[35]\\d|49)\\d{6}$/',
            'mobile' => '/^6(?:03|44|71|[1-356])\\d{6}$/',
            'tollfree' => '/^8[08]\\d{6}$/',
            'premium' => '/^9[0246]\\d{6}$/',
            'shared' => '/^8[12]\\d{6}$/',
            'uan' => '/^70[23]\\d{5}$/',
            'emergency' => '/^12[234]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,9}$/',
            'fixed' => '/^\\d{6,8}$/',
            'mobile' => '/^\\d{8,9}$/',
            'tollfree' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'shared' => '/^\\d{8}$/',
            'uan' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
