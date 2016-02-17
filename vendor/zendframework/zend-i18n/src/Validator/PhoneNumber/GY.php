<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '592',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-4679]\\d{6}$/',
            'fixed' => '/^(?:2(?:1[6-9]|2[0-35-9]|3[1-4]|5[3-9]|6\\d|7[0-24-79])|3(?:2[25-9]|3\\d)|4(?:4[0-24]|5[56])|77[1-57])\\d{4}$/',
            'mobile' => '/^6\\d{6}$/',
            'tollfree' => '/^(?:289|862)\\d{4}$/',
            'premium' => '/^9008\\d{3}$/',
            'shortcode' => '/^0(?:02|171|444|7[67]7|801|9(?:0[78]|[2-47]))$/',
            'emergency' => '/^91[123]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
