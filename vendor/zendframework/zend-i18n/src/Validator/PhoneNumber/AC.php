<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '247',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-467]\d{3}$/',
            'fixed' => '/^(?:[267]\d|3[0-5]|4[4-69])\d{2}$/',
            'emergency' => '/^911$/',
        ),
        'possible' => array(
            'general' => '/^\d{4}$/',
            'fixed' => '/^\d{4}$/',
            'emergency' => '/^\d{3}$/',
        ),
    ),
);
