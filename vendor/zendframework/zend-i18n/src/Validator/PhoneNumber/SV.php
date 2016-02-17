<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '503',
    'patterns' => array(
        'national' => array(
            'general' => '/^[267]\\d{7}|[89]\\d{6}(?:\\d{4})?$/',
            'fixed' => '/^2[1-6]\\d{6}$/',
            'mobile' => '/^[67]\\d{7}$/',
            'tollfree' => '/^800\\d{4}(?:\\d{4})?$/',
            'premium' => '/^900\\d{4}(?:\\d{4})?$/',
            'emergency' => '/^911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,8}|\\d{11}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{7}(?:\\d{4})?$/',
            'premium' => '/^\\d{7}(?:\\d{4})?$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
