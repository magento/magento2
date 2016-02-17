<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '33',
    'patterns' => array(
        'national' => array(
            'general' => '/^[124-9]\\d{8}|3\\d{3}(?:\\d{5})?$/',
            'fixed' => '/^[1-5]\\d{8}$/',
            'mobile' => '/^(?:[6-7]\\d{8}|7[5-9]\\d{7})$/',
            'tollfree' => '/^80\\d{7}$/',
            'premium' => '/^(?:3\\d{3}|89[1-37-9])\\d{6}$/',
            'shared' => '/^8(?:1[019]|2[0156]|84|90)\\d{6}$/',
            'voip' => '/^9\\d{8}$/',
            'emergency' => '/^1(?:[578]|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{4}(?:\\d{5})?$/',
            'fixed' => '/^\\d{9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{4}(?:\\d{5})?$/',
            'shared' => '/^\\d{9}$/',
            'voip' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
