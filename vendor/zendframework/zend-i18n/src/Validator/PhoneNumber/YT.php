<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '262',
    'patterns' => array(
        'national' => array(
            'general' => '/^[268]\\d{8}$/',
            'fixed' => '/^2696[0-4]\\d{4}$/',
            'mobile' => '/^639\\d{6}$/',
            'tollfree' => '/^80\\d{7}$/',
            'emergency' => '/^1(?:12|5)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
