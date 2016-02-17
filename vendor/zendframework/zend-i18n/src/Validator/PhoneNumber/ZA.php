<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '27',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-79]\\d{8}|8(?:[067]\\d{7}|[1-4]\\d{3,7})$/',
            'fixed' => '/^(?:1[0-8]|2[0-378]|3[1-69]|4\\d|5[1346-8])\\d{7}$/',
            'mobile' => '/^(?:6[0-5]|7[0-46-9])\\d{7}|8[1-4]\\d{3,7}$/',
            'tollfree' => '/^80\\d{7}$/',
            'premium' => '/^86[2-9]\\d{6}|90\\d{7}$/',
            'shared' => '/^860\\d{6}$/',
            'voip' => '/^87\\d{7}$/',
            'uan' => '/^861\\d{6}$/',
            'emergency' => '/^1(?:01(?:11|77)|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,9}$/',
            'fixed' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'shared' => '/^\\d{9}$/',
            'voip' => '/^\\d{9}$/',
            'uan' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3,5}$/',
        ),
    ),
);
