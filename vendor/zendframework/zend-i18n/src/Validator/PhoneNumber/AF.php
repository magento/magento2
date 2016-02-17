<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '93',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-7]\d{8}$/',
            'fixed' => '/^(?:[25][0-8]|[34][0-4]|6[0-5])[2-9]\d{6}$/',
            'mobile' => '/^7[057-9]\d{7}$/',
            'emergency' => '/^1(?:02|19)$/',
        ),
        'possible' => array(
            'general' => '/^\d{7,9}$/',
            'mobile' => '/^\d{9}$/',
            'emergency' => '/^\d{3}$/',
        ),
    ),
);
