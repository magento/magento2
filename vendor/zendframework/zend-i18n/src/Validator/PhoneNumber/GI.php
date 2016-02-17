<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '350',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2568]\\d{7}$/',
            'fixed' => '/^2(?:00\\d|16[0-7]|22[2457])\\d{4}$/',
            'mobile' => '/^(?:5[4-8]|60)\\d{6}$/',
            'tollfree' => '/^80\\d{6}$/',
            'premium' => '/^8[1-689]\\d{6}$/',
            'shared' => '/^87\\d{6}$/',
            'shortcode' => '/^1(?:00|1(?:6(?:00[06]|11[17])|8\\d{2})|23|4(?:1|7[014])|5[015]|9[34])|8(?:00|4[0-2]|8\\d)$/',
            'emergency' => '/^1(?:12|9[09])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'shortcode' => '/^\\d{3,6}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
