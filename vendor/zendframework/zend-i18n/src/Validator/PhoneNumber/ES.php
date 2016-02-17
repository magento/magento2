<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '34',
    'patterns' => array(
        'national' => array(
            'general' => '/^[5-9]\\d{8}$/',
            'fixed' => '/^8(?:[13]0|[28][0-8]|[47][1-9]|5[01346-9]|6[0457-9])\\d{6}|9(?:[1238][0-8]\\d{6}|4[1-9]\\d{6}|5\\d{7}|6(?:[0-8]\\d{6}|9(?:0(?:[0-57-9]\\d{4}|6(?:0[0-8]|1[1-9]|[2-9]\\d)\\d{2})|[1-9]\\d{5}))|7(?:[124-9]\\d{2}|3(?:[0-8]\\d|9[1-9]))\\d{4})$/',
            'mobile' => '/^(?:6\\d{6}|7[1-4]\\d{5}|9(?:6906(?:09|10)|7390\\d{2}))\\d{2}$/',
            'tollfree' => '/^[89]00\\d{6}$/',
            'premium' => '/^80[367]\\d{6}$/',
            'shared' => '/^90[12]\\d{6}$/',
            'personal' => '/^70\\d{7}$/',
            'uan' => '/^51\\d{7}$/',
            'emergency' => '/^0(?:[69][12]|8[05])|112$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
