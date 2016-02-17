<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '7',
    'patterns' => array(
        'national' => array(
            'general' => '/^(?:33\\d|7\\d{2}|80[09])\\d{7}$/',
            'fixed' => '/^33622\\d{5}|7(?:1(?:0(?:[23]\\d|4[023]|59|63)|1(?:[23]\\d|4[0-79]|59)|2(?:[23]\\d|59)|3(?:2\\d|3[1-79]|4[0-35-9]|59)|4(?:2\\d|3[013-79]|4[0-8]|5[1-79])|5(?:2\\d|3[1-8]|4[1-7]|59)|6(?:[234]\\d|5[19]|61)|72\\d|8(?:[27]\\d|3[1-46-9]|4[0-5]))|2(?:1(?:[23]\\d|4[46-9]|5[3469])|2(?:2\\d|3[0679]|46|5[12679])|3(?:[234]\\d|5[139])|4(?:2\\d|3[1235-9]|59)|5(?:[23]\\d|4[01246-8]|59|61)|6(?:2\\d|3[1-9]|4[0-4]|59)|7(?:[237]\\d|40|5[279])|8(?:[23]\\d|4[0-3]|59)|9(?:2\\d|3[124578]|59)))\\d{5}$/',
            'mobile' => '/^7(?:0[01257]|6[02-4]|7[1578]|85)\\d{7}$/',
            'tollfree' => '/^800\\d{7}$/',
            'premium' => '/^809\\d{7}$/',
            'voip' => '/^751\\d{7}$/',
            'emergency' => '/^1(?:0[123]|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
