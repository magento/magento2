<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '58',
    'patterns' => array(
        'national' => array(
            'general' => '/^[24589]\\d{9}$/',
            'fixed' => '/^(?:2(?:12|3[457-9]|[58][1-9]|[467]\\d|9[1-6])|50[01])\\d{7}$/',
            'mobile' => '/^4(?:1[24-8]|2[46])\\d{7}$/',
            'tollfree' => '/^800\\d{7}$/',
            'premium' => '/^900\\d{7}$/',
            'emergency' => '/^171$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,10}$/',
            'mobile' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
