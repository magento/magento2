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
            'general' => '/^[3589]\\d{9}$/',
            'fixed' => '/^340(?:2(?:01|2[067]|36|44|77)|3(?:32|44)|4(?:4[38]|7[34])|5(?:1[34]|55)|6(?:26|4[23]|9[023])|7(?:[17]\\d|27)|884|998)\\d{4}$/',
            'mobile' => '/^340(?:2(?:01|2[067]|36|44|77)|3(?:32|44)|4(?:4[38]|7[34])|5(?:1[34]|55)|6(?:26|4[23]|9[023])|7(?:[17]\\d|27)|884|998)\\d{4}$/',
            'tollfree' => '/^8(?:00|55|66|77|88)[2-9]\\d{6}$/',
            'premium' => '/^900[2-9]\\d{6}$/',
            'personal' => '/^5(?:00|33|44)[2-9]\\d{6}$/',
            'emergency' => '/^911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}(?:\\d{3})?$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'personal' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
