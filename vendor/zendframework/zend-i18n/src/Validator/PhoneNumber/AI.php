<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '1',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2589]\d{9}$/',
            'fixed' => '/^2644(?:6[12]|9[78])\d{4}$/',
            'mobile' => '/^264(?:235|476|5(?:3[6-9]|8[1-4])|7(?:29|72))\d{4}$/',
            'tollfree' => '/^8(?:00|55|66|77|88)[2-9]\d{6}$/',
            'premium' => '/^900[2-9]\d{6}$/',
            'personal' => '/^5(?:00|33|44)[2-9]\d{6}$/',
            'emergency' => '/^911$/',
        ),
        'possible' => array(
            'general' => '/^\d{7}(?:\d{3})?$/',
            'mobile' => '/^\d{10}$/',
            'tollfree' => '/^\d{10}$/',
            'premium' => '/^\d{10}$/',
            'personal' => '/^\d{10}$/',
            'emergency' => '/^\d{3}$/',
        ),
    ),
);
