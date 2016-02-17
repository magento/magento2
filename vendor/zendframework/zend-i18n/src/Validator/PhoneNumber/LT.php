<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '370',
    'patterns' => array(
        'national' => array(
            'general' => '/^[3-9]\\d{7}$/',
            'fixed' => '/^(?:3[1478]|4[124-6]|52)\\d{6}$/',
            'mobile' => '/^6\\d{7}$/',
            'tollfree' => '/^800\\d{5}$/',
            'premium' => '/^9(?:0[0239]|10)\\d{5}$/',
            'personal' => '/^700\\d{5}$/',
            'shared' => '/^808\\d{5}$/',
            'uan' => '/^70[67]\\d{5}$/',
            'emergency' => '/^0(?:11?|22?|33?)|1(?:0[123]|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
