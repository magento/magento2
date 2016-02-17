<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '676',
    'patterns' => array(
        'national' => array(
            'general' => '/^[02-8]\\d{4,6}$/',
            'fixed' => '/^(?:2\\d|3[1-8]|4[1-4]|[56]0|7[0149]|8[05])\\d{3}$/',
            'mobile' => '/^(?:7[578]|8[7-9])\\d{5}$/',
            'tollfree' => '/^0800\\d{3}$/',
            'emergency' => '/^9(?:11|22|33|99)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,7}$/',
            'fixed' => '/^\\d{5}$/',
            'mobile' => '/^\\d{7}$/',
            'tollfree' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
