<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '235',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2679]\\d{7}$/',
            'fixed' => '/^22(?:[3789]0|5[0-5]|6[89])\\d{4}$/',
            'mobile' => '/^(?:6[36]\\d|77\\d|9(?:5[0-4]|9\\d))\\d{5}$/',
            'emergency' => '/^1[78]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
