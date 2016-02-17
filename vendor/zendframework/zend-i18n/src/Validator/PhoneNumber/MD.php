<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '373',
    'patterns' => array(
        'national' => array(
            'general' => '/^[235-9]\\d{7}$/',
            'fixed' => '/^(?:2(?:1[0569]|2\\d|3[015-7]|4[1-46-9]|5[0-24689]|6[2-589]|7[1-37]|9[1347-9])|5(?:33|5[257]))\\d{5}$/',
            'mobile' => '/^(?:562|6(?:50|7[1-5]|[089]\\d)|7(?:7[47-9]|[89]\\d))\\d{5}$/',
            'tollfree' => '/^800\\d{5}$/',
            'premium' => '/^90[056]\\d{5}$/',
            'shared' => '/^808\\d{5}$/',
            'uan' => '/^8(?:03|14)\\d{5}$/',
            'voip' => '/^3[08]\\d{6}$/',
            'shortcode' => '/^1(?:1(?:[79]|6(?:000|1(?:11|23))|8\\d)|4\\d{3}|5[0-3]\\d|6[0-389]\\d|8\\d{2}|9(?:0[04-9]|[1-4]\\d))$/',
            'emergency' => '/^112|90[123]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'shortcode' => '/^\\d{3,6}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
