<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '971',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-79]\d{7,8}|800\d{2,9}$/',
            'fixed' => '/^[2-4679][2-8]\d{6}$/',
            'mobile' => '/^5[0256]\d{7}$/',
            'tollfree' => '/^400\d{6}|800\d{2,9}$/',
            'premium' => '/^900[02]\d{5}$/',
            'shared' => '/^700[05]\d{5}$/',
            'uan' => '/^600[25]\d{5}$/',
            'emergency' => '/^112|99[789]$/',
        ),
        'possible' => array(
            'general' => '/^\d{5,12}$/',
            'fixed' => '/^\d{7,8}$/',
            'mobile' => '/^\d{9}$/',
            'tollfree' => '/^\d{5,12}$/',
            'premium' => '/^\d{9}$/',
            'shared' => '/^\d{9}$/',
            'uan' => '/^\d{9}$/',
            'emergency' => '/^\d{3}$/',
        ),
    ),
);
