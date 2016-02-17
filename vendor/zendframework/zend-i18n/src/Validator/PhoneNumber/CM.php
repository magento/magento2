<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '237',
    'patterns' => array(
        'national' => array(
            'general' => '/^[237-9]\\d{7}$/',
            'fixed' => '/^(?:22|33)\\d{6}$/',
            'mobile' => '/^[79]\\d{7}$/',
            'tollfree' => '/^800\\d{5}$/',
            'premium' => '/^88\\d{6}$/',
            'emergency' => '/^1?1[37]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
