<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '297',
    'patterns' => array(
        'national' => array(
            'general' => '/^[25-9]\\d{6}$/',
            'fixed' => '/^5(?:2\\d|8[1-9])\\d{4}$/',
            'mobile' => '/^(?:5(?:6\\d|9[2-478])|6(?:[039]0|22|4[01]|6[0-2])|7[34]\\d|9(?:6[45]|9[4-8]))\\d{4}$/',
            'tollfree' => '/^800\\d{4}$/',
            'premium' => '/^900\\d{4}$/',
            'voip' => '/^28\\d{5}|501\\d{4}$/',
            'emergency' => '/^100|911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
