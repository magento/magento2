<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '60',
    'patterns' => array(
        'national' => array(
            'general' => '/^[13-9]\\d{7,9}$/',
            'fixed' => '/^(?:3[2-9]\\d|[4-9][2-9])\\d{6}$/',
            'mobile' => '/^1(?:1[1-3]\\d{2}|[02-4679][2-9]\\d|8(?:1[23]|[2-9]\\d))\\d{5}$/',
            'tollfree' => '/^1[38]00\\d{6}$/',
            'premium' => '/^1600\\d{6}$/',
            'personal' => '/^1700\\d{6}$/',
            'voip' => '/^154\\d{7}$/',
            'emergency' => '/^112|999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,10}$/',
            'fixed' => '/^\\d{6,9}$/',
            'mobile' => '/^\\d{9,10}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'personal' => '/^\\d{10}$/',
            'voip' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
