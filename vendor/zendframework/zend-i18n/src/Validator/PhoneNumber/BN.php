<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '673',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-578]\\d{6}$/',
            'fixed' => '/^[2-5]\\d{6}$/',
            'mobile' => '/^[78]\\d{6}$/',
            'emergency' => '/^99[135]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
