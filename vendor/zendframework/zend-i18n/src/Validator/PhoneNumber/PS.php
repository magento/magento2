<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '970',
    'patterns' => array(
        'national' => array(
            'general' => '/^[24589]\\d{7,8}|1(?:[78]\\d{8}|[49]\\d{2,3})$/',
            'fixed' => '/^(?:22[234789]|42[45]|82[01458]|92[369])\\d{5}$/',
            'mobile' => '/^5[69]\\d{7}$/',
            'tollfree' => '/^1800\\d{6}$/',
            'premium' => '/^1(?:4|9\\d)\\d{2}$/',
            'shared' => '/^1700\\d{6}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{4,10}$/',
            'fixed' => '/^\\d{7,8}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{4,5}$/',
            'shared' => '/^\\d{10}$/',
        ),
    ),
);
