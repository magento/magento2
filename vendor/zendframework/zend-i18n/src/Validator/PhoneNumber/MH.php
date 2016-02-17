<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '692',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-6]\\d{6}$/',
            'fixed' => '/^(?:247|528|625)\\d{4}$/',
            'mobile' => '/^(?:235|329|45[56]|545)\\d{4}$/',
            'voip' => '/^635\\d{4}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
        ),
    ),
);
