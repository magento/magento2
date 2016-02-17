<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '218',
    'patterns' => array(
        'national' => array(
            'general' => '/^[25679]\\d{8}$/',
            'fixed' => '/^(?:2[1345]|5[1347]|6[123479]|71)\\d{7}$/',
            'mobile' => '/^9[1-6]\\d{7}$/',
            'emergency' => '/^19[013]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
