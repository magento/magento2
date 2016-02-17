<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '375',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-4]\\d{8}|[89]\\d{9,10}$/',
            'fixed' => '/^(?:1(?:5(?:1[1-5]|2\\d|6[2-4]|9[1-7])|6(?:[235]\\d|4[1-7])|7\\d{2})|2(?:1(?:[246]\\d|3[0-35-9]|5[1-9])|2(?:[235]\\d|4[0-8])|3(?:2\\d|3[02-79]|4[024-7]|5[0-7])))\\d{5}$/',
            'mobile' => '/^(?:2(?:5[5679]|9[1-9])|33\\d|44\\d)\\d{6}$/',
            'tollfree' => '/^8(?:0[13]|20\\d)\\d{7}$/',
            'premium' => '/^(?:810|902)\\d{7}$/',
            'emergency' => '/^1(?:0[123]|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,11}$/',
            'fixed' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{10,11}$/',
            'premium' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
