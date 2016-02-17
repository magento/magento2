<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '244',
    'patterns' => array(
        'national' => array(
            'general' => '/^[29]\d{8}$/',
            'fixed' => '/^2\d(?:[26-9]\d|\d[26-9])\d{5}$/',
            'mobile' => '/^9[1-4]\d{7}$/',
            'emergency' => '/^11[235]$/',
        ),
        'possible' => array(
            'general' => '/^\d{9}$/',
            'emergency' => '/^\d{3}$/',
        ),
    ),
);
