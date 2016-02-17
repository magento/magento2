<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '256',
    'patterns' => array(
        'national' => array(
            'general' => '/^\\d{9}$/',
            'fixed' => '/^20(?:[014]\\d{2}|2(?:40|[5-9]\\d)|3[23]\\d|5[0-4]\\d)\\d{4}|[34]\\d{8}$/',
            'mobile' => '/^7(?:0[0-7]|[15789]\\d|20|[46][0-4])\\d{6}$/',
            'tollfree' => '/^800[123]\\d{5}$/',
            'premium' => '/^90[123]\\d{6}$/',
            'emergency' => '/^999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,9}$/',
            'fixed' => '/^\\d{5,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
