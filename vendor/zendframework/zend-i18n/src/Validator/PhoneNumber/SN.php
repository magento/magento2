<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '221',
    'patterns' => array(
        'national' => array(
            'general' => '/^[37]\\d{8}$/',
            'fixed' => '/^3(?:0(?:1[01]|80)|3(?:8[1-9]|9[2-9]))\\d{5}$/',
            'mobile' => '/^7(?:0(?:[01279]0|3[03]|4[05]|5[06]|6[03-5]|8[029])|6(?:1[23]|2[89]|3[3489]|4[6-9]|5\\d|6[3-9]|7[45]|8[3-8])|7\\d{2}|8(?:01|1[01]))\\d{5}$/',
            'voip' => '/^33301\\d{4}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
        ),
    ),
);
