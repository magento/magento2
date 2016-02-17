<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '963',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-59]\\d{7,8}$/',
            'fixed' => '/^(?:1(?:1\\d?|4\\d|[2356])|2[1-35]|3(?:[13]\\d|4)|4[13]|5[1-3])\\d{6}$/',
            'mobile' => '/^9(?:22|[35][0-8]|4\\d|6[024-9]|88|9[0-489])\\d{6}$/',
            'emergency' => '/^11[023]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
