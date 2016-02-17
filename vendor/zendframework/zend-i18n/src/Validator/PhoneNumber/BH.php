<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '973',
    'patterns' => array(
        'national' => array(
            'general' => '/^[136-9]\\d{7}$/',
            'fixed' => '/^(?:1(?:3[3-6]|6[0156]|7\\d)\\d|6(?:1[16]\\d|6(?:0\\d|3[12]|44)|9(?:69|9[6-9]))|77\\d{2})\\d{4}$/',
            'mobile' => '/^(?:3(?:[23469]\\d|5[35]|77|8[348])\\d|6(?:1[16]\\d|6(?:[06]\\d|3[03-9]|44)|9(?:69|9[6-9]))|77\\d{2})\\d{4}$/',
            'tollfree' => '/^80\\d{6}$/',
            'premium' => '/^(?:87|9[014578])\\d{6}$/',
            'shared' => '/^84\\d{6}$/',
            'emergency' => '/^999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
