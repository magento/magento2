<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '265',
    'patterns' => array(
        'national' => array(
            'general' => '/^(?:1(?:\\d{2})?|[2789]\\d{2})\\d{6}$/',
            'fixed' => '/^(?:1[2-9]|21\\d{2})\\d{5}$/',
            'mobile' => '/^(?:111|77\\d|88\\d|99\\d)\\d{6}$/',
            'emergency' => '/^199|99[789]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
