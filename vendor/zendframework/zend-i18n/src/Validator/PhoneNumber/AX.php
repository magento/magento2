<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '358',
    'patterns' => array(
        'national' => array(
            'general' => '/^[135]\\d{5,9}|[27]\\d{4,9}|4\\d{5,10}|6\\d{7,8}|8\\d{6,9}$/',
            'fixed' => '/^18[1-8]\\d{3,9}$/',
            'mobile' => '/^4\\d{5,10}|50\\d{4,8}$/',
            'tollfree' => '/^800\\d{4,7}$/',
            'premium' => '/^[67]00\\d{5,6}$/',
            'uan' => '/^[13]0\\d{4,8}|2(?:0(?:[016-8]\\d{3,7}|[2-59]\\d{2,7})|9\\d{4,8})|60(?:[12]\\d{5,6}|6\\d{7})|7(?:1\\d{7}|3\\d{8}|5[03-9]\\d{2,7})$/',
            'emergency' => '/^112$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,12}$/',
            'fixed' => '/^\\d{6,12}$/',
            'mobile' => '/^\\d{6,11}$/',
            'tollfree' => '/^\\d{7,10}$/',
            'premium' => '/^\\d{8,9}$/',
            'uan' => '/^\\d{5,10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
