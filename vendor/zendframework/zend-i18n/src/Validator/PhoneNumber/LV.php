<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '371',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2689]\\d{7}$/',
            'fixed' => '/^6[3-8]\\d{6}$/',
            'mobile' => '/^2\\d{7}$/',
            'tollfree' => '/^80\\d{6}$/',
            'premium' => '/^90\\d{6}$/',
            'shared' => '/^81\\d{6}$/',
            'emergency' => '/^0[123]|112$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
