<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '254',
    'patterns' => array(
        'national' => array(
            'general' => '/^20\\d{6,7}|[4-9]\\d{6,9}$/',
            'fixed' => '/^20\\d{6,7}|4(?:[013]\\d{7}|[24-6]\\d{5,7})|5(?:[0-36-8]\\d{5,7}|[459]\\d{5})|6(?:[08]\\d{5}|[14-79]\\d{5,7}|2\\d{7})$/',
            'mobile' => '/^7(?:0[0-8]|[123]\\d|5[0-6]|7[0-5]|8[5-9])\\d{6}$/',
            'tollfree' => '/^800[24-8]\\d{5,6}$/',
            'premium' => '/^900[02-578]\\d{5}$/',
            'shortcode' => '/^1(?:0[09]|1(?:[06]|9[0-2579])|2[13]|3[01])$/',
            'emergency' => '/^112|999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,10}$/',
            'fixed' => '/^\\d{5,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9,10}$/',
            'premium' => '/^\\d{9}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
