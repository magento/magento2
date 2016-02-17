<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '357',
    'patterns' => array(
        'national' => array(
            'general' => '/^[257-9]\\d{7}$/',
            'fixed' => '/^2[2-6]\\d{6}$/',
            'mobile' => '/^9[5-79]\\d{6}$/',
            'tollfree' => '/^800\\d{5}$/',
            'premium' => '/^90[09]\\d{5}$/',
            'shared' => '/^80[1-9]\\d{5}$/',
            'personal' => '/^700\\d{5}$/',
            'uan' => '/^(?:50|77)\\d{6}$/',
            'emergency' => '/^1(?:12|99)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
