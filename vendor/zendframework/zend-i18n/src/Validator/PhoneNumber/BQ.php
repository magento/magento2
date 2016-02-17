<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '599',
    'patterns' => array(
        'national' => array(
            'general' => '/^[347]\\d{6}$/',
            'fixed' => '/^(?:318[023]|416[0239]|7(?:1[578]|50)\\d)\\d{3}$/',
            'mobile' => '/^(?:318[1456]|416[15-8]|7(?:0[01]|[89]\\d)\\d)\\d{3}|$/',
            'emergency' => '/^112|911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
