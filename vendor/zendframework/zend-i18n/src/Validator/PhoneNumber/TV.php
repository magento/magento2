<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '688',
    'patterns' => array(
        'national' => array(
            'general' => '/^[29]\\d{4,5}$/',
            'fixed' => '/^2[02-9]\\d{3}$/',
            'mobile' => '/^90\\d{4}$/',
            'emergency' => '/^911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,6}$/',
            'fixed' => '/^\\d{5}$/',
            'mobile' => '/^\\d{6}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
