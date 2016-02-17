<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '232',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-578]\\d{7}$/',
            'fixed' => '/^[235]2[2-4][2-9]\\d{4}$/',
            'mobile' => '/^(?:2[15]|3[034]|4[04]|5[05]|7[6-9]|88)\\d{6}$/',
            'emergency' => '/^(?:01|99)9$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
