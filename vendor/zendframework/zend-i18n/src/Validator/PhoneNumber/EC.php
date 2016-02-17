<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '593',
    'patterns' => array(
        'national' => array(
            'general' => '/^1\\d{9,10}|[2-8]\\d{7}|9\\d{8}$/',
            'fixed' => '/^[2-7][2-7]\\d{6}$/',
            'mobile' => '/^9(?:[2-7]9|[89]\\d)\\d{6}$/',
            'tollfree' => '/^1800\\d{6,7}$/',
            'voip' => '/^[2-7]890\\d{4}$/',
            'emergency' => '/^1(?:0[12]|12)|911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,11}$/',
            'fixed' => '/^\\d{7,8}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{10,11}$/',
            'voip' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
