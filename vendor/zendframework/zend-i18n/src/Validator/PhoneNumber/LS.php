<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '266',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2568]\\d{7}$/',
            'fixed' => '/^2\\d{7}$/',
            'mobile' => '/^[56]\\d{7}$/',
            'tollfree' => '/^800[256]\\d{4}$/',
            'emergency' => '/^11[257]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
