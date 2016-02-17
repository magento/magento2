<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '257',
    'patterns' => array(
        'national' => array(
            'general' => '/^[27]\\d{7}$/',
            'fixed' => '/^22(?:2[0-7]|[3-5]0)\\d{4}$/',
            'mobile' => '/^(?:29\\d|7(?:1[1-3]|[4-9]\\d))\\d{5}$/',
            'emergency' => '/^11[78]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
