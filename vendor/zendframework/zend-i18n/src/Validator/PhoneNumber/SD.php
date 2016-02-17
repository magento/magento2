<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '249',
    'patterns' => array(
        'national' => array(
            'general' => '/^[19]\\d{8}$/',
            'fixed' => '/^1(?:[125]\\d|8[3567])\\d{6}$/',
            'mobile' => '/^9[012569]\\d{7}$/',
            'emergency' => '/^999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
