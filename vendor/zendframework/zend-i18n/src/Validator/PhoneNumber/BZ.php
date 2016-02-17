<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '501',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-8]\\d{6}|0\\d{10}$/',
            'fixed' => '/^[234578][02]\\d{5}$/',
            'mobile' => '/^6[0-367]\\d{5}$/',
            'tollfree' => '/^0800\\d{7}$/',
            'emergency' => '/^9(?:0|11)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}(?:\\d{4})?$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{7}$/',
            'tollfree' => '/^\\d{11}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
