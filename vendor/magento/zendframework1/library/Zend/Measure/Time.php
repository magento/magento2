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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * Implement needed classes
 */
#require_once 'Zend/Measure/Abstract.php';
#require_once 'Zend/Locale.php';

/**
 * Class for handling time conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Time
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Time extends Zend_Measure_Abstract
{
    const STANDARD = 'SECOND';

    const ANOMALISTIC_YEAR  = 'ANOMALISTIC_YEAR';
    const ATTOSECOND        = 'ATTOSECOND';
    const CENTURY           = 'CENTURY';
    const DAY               = 'DAY';
    const DECADE            = 'DECADE';
    const DRACONIC_YEAR     = 'DRACONTIC_YEAR';
    const EXASECOND         = 'EXASECOND';
    const FEMTOSECOND       = 'FEMTOSECOND';
    const FORTNIGHT         = 'FORTNIGHT';
    const GAUSSIAN_YEAR     = 'GAUSSIAN_YEAR';
    const GIGASECOND        = 'GIGASECOND';
    const GREGORIAN_YEAR    = 'GREGORIAN_YEAR';
    const HOUR              = 'HOUR';
    const JULIAN_YEAR       = 'JULIAN_YEAR';
    const KILOSECOND        = 'KILOSECOND';
    const LEAPYEAR          = 'LEAPYEAR';
    const MEGASECOND        = 'MEGASECOND';
    const MICROSECOND       = 'MICROSECOND';
    const MILLENIUM         = 'MILLENIUM';
    const MILLISECOND       = 'MILLISECOND';
    const MINUTE            = 'MINUTE';
    const MONTH             = 'MONTH';
    const NANOSECOND        = 'NANOSECOND';
    const PETASECOND        = 'PETASECOND';
    const PICOSECOND        = 'PICOSECOND';
    const QUARTER           = 'QUARTER';
    const SECOND            = 'SECOND';
    const SHAKE             = 'SHAKE';
    const SIDEREAL_YEAR     = 'SYNODIC_MONTH';
    const TERASECOND        = 'TERASECOND';
    const TROPICAL_YEAR     = 'TROPIC_YEAR';
    const WEEK              = 'WEEK';
    const YEAR              = 'YEAR';

    /**
     * Calculations for all time units
     *
     * @var array
     */
    protected $_units = array(
        'ANOMALISTIC_YEAR'  => array('31558432', 'anomalistic year'),
        'ATTOSECOND'        => array('1.0e-18', 'as'),
        'CENTURY'           => array('3153600000', 'century'),
        'DAY'               => array('86400', 'day'),
        'DECADE'            => array('315360000', 'decade'),
        'DRACONIC_YEAR'     => array('29947974', 'draconic year'),
        'EXASECOND'         => array('1.0e+18', 'Es'),
        'FEMTOSECOND'       => array('1.0e-15', 'fs'),
        'FORTNIGHT'         => array('1209600', 'fortnight'),
        'GAUSSIAN_YEAR'     => array('31558196', 'gaussian year'),
        'GIGASECOND'        => array('1.0e+9', 'Gs'),
        'GREAT_YEAR'        => array(array('*' => '31536000', '*' => '25700'), 'great year'),
        'GREGORIAN_YEAR'    => array('31536000', 'year'),
        'HOUR'              => array('3600', 'h'),
        'JULIAN_YEAR'       => array('31557600', 'a'),
        'KILOSECOND'        => array('1000', 'ks'),
        'LEAPYEAR'          => array('31622400', 'year'),
        'MEGASECOND'        => array('1000000', 'Ms'),
        'MICROSECOND'       => array('0.000001', 'µs'),
        'MILLENIUM'         => array('31536000000', 'millenium'),
        'MILLISECOND'       => array('0.001', 'ms'),
        'MINUTE'            => array('60', 'min'),
        'MONTH'             => array('2628600', 'month'),
        'NANOSECOND'        => array('1.0e-9', 'ns'),
        'PETASECOND'        => array('1.0e+15', 'Ps'),
        'PICOSECOND'        => array('1.0e-12', 'ps'),
        'QUARTER'           => array('7884000', 'quarter'),
        'SECOND'            => array('1', 's'),
        'SHAKE'             => array('1.0e-9', 'shake'),
        'SIDEREAL_YEAR'     => array('31558149.7676', 'sidereal year'),
        'TERASECOND'        => array('1.0e+12', 'Ts'),
        'TROPICAL_YEAR'     => array('31556925', 'tropical year'),
        'WEEK'              => array('604800', 'week'),
        'YEAR'              => array('31536000', 'year'),
        'STANDARD'          => 'SECOND'
    );
}
