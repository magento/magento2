<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '376',
    'patterns' => array(
        'national' => array(
            'general' => '/^(?:[346-9]|180)\d{5}$/',
            'fixed' => '/^[78]\d{5}$/',
            'mobile' => '/^[346]\d{5}$/',
            'tollfree' => '/^180[02]\d{4}$/',
            'premium' => '/^9\d{5}$/',
            'emergency' => '/^11[0268]$/',
        ),
        'possible' => array(
            'general' => '/^\d{6,8}$/',
            'fixed' => '/^\d{6}$/',
            'mobile' => '/^\d{6}$/',
            'tollfree' => '/^\d{8}$/',
            'premium' => '/^\d{6}$/',
            'emergency' => '/^\d{3}$/',
        ),
    ),
);
