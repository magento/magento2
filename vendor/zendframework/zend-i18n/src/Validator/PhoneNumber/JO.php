<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '962',
    'patterns' => array(
        'national' => array(
            'general' => '/^[235-9]\\d{7,8}$/',
            'fixed' => '/^(?:2(?:6(?:2[0-35-9]|3[0-57-8]|4[24-7]|5[0-24-8]|[6-9][02])|7(?:0[1-79]|10|2[014-7]|3[0-689]|4[019]|5[0-3578]))|32(?:0[1-69]|1[1-35-7]|2[024-7]|3\\d|[457][02]|60)|53(?:[013][02]|2[0-59]|49|5[0-35-9]|6[15]|7[45]|8[1-6]|9[0-36-9])|6(?:2[50]0|300|4(?:0[0125]|1[2-7]|2[0569]|[38][07-9]|4[025689]|6[0-589]|7\\d|9[0-2])|5(?:[01][056]|2[034]|3[0-57-9]|4[17-8]|5[0-69]|6[0-35-9]|7[1-379]|8[0-68]|9[02-39]))|87(?:[02]0|7[08]|9[09]))\\d{4}$/',
            'mobile' => '/^7(?:55|7[25-9]|8[5-9]|9[05-9])\\d{6}$/',
            'pager' => '/^74(?:66|77)\\d{5}$/',
            'tollfree' => '/^80\\d{6}$/',
            'premium' => '/^900\\d{5}$/',
            'shared' => '/^85\\d{6}$/',
            'personal' => '/^70\\d{7}$/',
            'uan' => '/^8(?:10|8\\d)\\d{5}$/',
            'shortcode' => '/^1(?:09|1[01]|9[024-79])$/',
            'emergency' => '/^1(?:12|91)|911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'fixed' => '/^\\d{7,8}$/',
            'mobile' => '/^\\d{9}$/',
            'pager' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'shared' => '/^\\d{8}$/',
            'personal' => '/^\\d{9}$/',
            'uan' => '/^\\d{8}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
