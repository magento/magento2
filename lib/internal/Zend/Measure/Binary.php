<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category  Zend
 * @package   Zend_Measure
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Binary.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Implement needed classes
 */
#require_once 'Zend/Measure/Abstract.php';
#require_once 'Zend/Locale.php';

/**
 * Class for handling binary conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Binary
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Binary extends Zend_Measure_Abstract
{
    const STANDARD = 'BYTE';

    const BIT               = 'BIT';
    const CRUMB             = 'CRUMB';
    const NIBBLE            = 'NIBBLE';
    const BYTE              = 'BYTE';
    const KILOBYTE          = 'KILOBYTE';
    const KIBIBYTE          = 'KIBIBYTE';
    const KILO_BINARY_BYTE  = 'KILO_BINARY_BYTE';
    const KILOBYTE_SI       = 'KILOBYTE_SI';
    const MEGABYTE          = 'MEGABYTE';
    const MEBIBYTE          = 'MEBIBYTE';
    const MEGA_BINARY_BYTE  = 'MEGA_BINARY_BYTE';
    const MEGABYTE_SI       = 'MEGABYTE_SI';
    const GIGABYTE          = 'GIGABYTE';
    const GIBIBYTE          = 'GIBIBYTE';
    const GIGA_BINARY_BYTE  = 'GIGA_BINARY_BYTE';
    const GIGABYTE_SI       = 'GIGABYTE_SI';
    const TERABYTE          = 'TERABYTE';
    const TEBIBYTE          = 'TEBIBYTE';
    const TERA_BINARY_BYTE  = 'TERA_BINARY_BYTE';
    const TERABYTE_SI       = 'TERABYTE_SI';
    const PETABYTE          = 'PETABYTE';
    const PEBIBYTE          = 'PEBIBYTE';
    const PETA_BINARY_BYTE  = 'PETA_BINARY_BYTE';
    const PETABYTE_SI       = 'PETABYTE_SI';
    const EXABYTE           = 'EXABYTE';
    const EXBIBYTE          = 'EXBIBYTE';
    const EXA_BINARY_BYTE   = 'EXA_BINARY_BYTE';
    const EXABYTE_SI        = 'EXABYTE_SI';
    const ZETTABYTE         = 'ZETTABYTE';
    const ZEBIBYTE          = 'ZEBIBYTE';
    const ZETTA_BINARY_BYTE = 'ZETTA_BINARY_BYTE';
    const ZETTABYTE_SI      = 'ZETTABYTE_SI';
    const YOTTABYTE         = 'YOTTABYTE';
    const YOBIBYTE          = 'YOBIBYTE';
    const YOTTA_BINARY_BYTE = 'YOTTA_BINARY_BYTE';
    const YOTTABYTE_SI      = 'YOTTABYTE_SI';

    /**
     * Calculations for all binary units
     *
     * @var array
     */
    protected $_units = array(
        'BIT'              => array('0.125',                     'b'),
        'CRUMB'            => array('0.25',                      'crumb'),
        'NIBBLE'           => array('0.5',                       'nibble'),
        'BYTE'             => array('1',                         'B'),
        'KILOBYTE'         => array('1024',                      'kB'),
        'KIBIBYTE'         => array('1024',                      'KiB'),
        'KILO_BINARY_BYTE' => array('1024',                      'KiB'),
        'KILOBYTE_SI'      => array('1000',                      'kB.'),
        'MEGABYTE'         => array('1048576',                   'MB'),
        'MEBIBYTE'         => array('1048576',                   'MiB'),
        'MEGA_BINARY_BYTE' => array('1048576',                   'MiB'),
        'MEGABYTE_SI'      => array('1000000',                   'MB.'),
        'GIGABYTE'         => array('1073741824',                'GB'),
        'GIBIBYTE'         => array('1073741824',                'GiB'),
        'GIGA_BINARY_BYTE' => array('1073741824',                'GiB'),
        'GIGABYTE_SI'      => array('1000000000',                'GB.'),
        'TERABYTE'         => array('1099511627776',             'TB'),
        'TEBIBYTE'         => array('1099511627776',             'TiB'),
        'TERA_BINARY_BYTE' => array('1099511627776',             'TiB'),
        'TERABYTE_SI'      => array('1000000000000',             'TB.'),
        'PETABYTE'         => array('1125899906842624',          'PB'),
        'PEBIBYTE'         => array('1125899906842624',          'PiB'),
        'PETA_BINARY_BYTE' => array('1125899906842624',          'PiB'),
        'PETABYTE_SI'      => array('1000000000000000',          'PB.'),
        'EXABYTE'          => array('1152921504606846976',       'EB'),
        'EXBIBYTE'         => array('1152921504606846976',       'EiB'),
        'EXA_BINARY_BYTE'  => array('1152921504606846976',       'EiB'),
        'EXABYTE_SI'       => array('1000000000000000000',       'EB.'),
        'ZETTABYTE'        => array('1180591620717411303424',    'ZB'),
        'ZEBIBYTE'         => array('1180591620717411303424',    'ZiB'),
        'ZETTA_BINARY_BYTE'=> array('1180591620717411303424',    'ZiB'),
        'ZETTABYTE_SI'     => array('1000000000000000000000',    'ZB.'),
        'YOTTABYTE'        => array('1208925819614629174706176', 'YB'),
        'YOBIBYTE'         => array('1208925819614629174706176', 'YiB'),
        'YOTTA_BINARY_BYTE'=> array('1208925819614629174706176', 'YiB'),
        'YOTTABYTE_SI'     => array('1000000000000000000000000', 'YB.'),
        'STANDARD'         => 'BYTE'
    );
}
