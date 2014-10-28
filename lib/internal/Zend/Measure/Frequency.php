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
 * @version   $Id: Frequency.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Implement needed classes
 */
#require_once 'Zend/Measure/Abstract.php';
#require_once 'Zend/Locale.php';

/**
 * Class for handling flow volume conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Frequency
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Frequency extends Zend_Measure_Abstract
{
    const STANDARD = 'HERTZ';

    const ONE_PER_SECOND        = 'ONE_PER_SECOND';
    const CYCLE_PER_SECOND      = 'CYCLE_PER_SECOND';
    const DEGREE_PER_HOUR       = 'DEGREE_PER_HOUR';
    const DEGREE_PER_MINUTE     = 'DEGREE_PER_MINUTE';
    const DEGREE_PER_SECOND     = 'DEGREE_PER_SECOND';
    const GIGAHERTZ             = 'GIGAHERTZ';
    const HERTZ                 = 'HERTZ';
    const KILOHERTZ             = 'KILOHERTZ';
    const MEGAHERTZ             = 'MEGAHERTZ';
    const MILLIHERTZ            = 'MILLIHERTZ';
    const RADIAN_PER_HOUR       = 'RADIAN_PER_HOUR';
    const RADIAN_PER_MINUTE     = 'RADIAN_PER_MINUTE';
    const RADIAN_PER_SECOND     = 'RADIAN_PER_SECOND';
    const REVOLUTION_PER_HOUR   = 'REVOLUTION_PER_HOUR';
    const REVOLUTION_PER_MINUTE = 'REVOLUTION_PER_MINUTE';
    const REVOLUTION_PER_SECOND = 'REVOLUTION_PER_SECOND';
    const RPM                   = 'RPM';
    const TERRAHERTZ            = 'TERRAHERTZ';

    /**
     * Calculations for all frequency units
     *
     * @var array
     */
    protected $_units = array(
        'ONE_PER_SECOND'        => array('1',             '1/s'),
        'CYCLE_PER_SECOND'      => array('1',             'cps'),
        'DEGREE_PER_HOUR'       => array(array('' => '1', '/' => '1296000'), '°/h'),
        'DEGREE_PER_MINUTE'     => array(array('' => '1', '/' => '21600'),   '°/m'),
        'DEGREE_PER_SECOND'     => array(array('' => '1', '/' => '360'),     '°/s'),
        'GIGAHERTZ'             => array('1000000000',    'GHz'),
        'HERTZ'                 => array('1',             'Hz'),
        'KILOHERTZ'             => array('1000',          'kHz'),
        'MEGAHERTZ'             => array('1000000',       'MHz'),
        'MILLIHERTZ'            => array('0.001',         'mHz'),
        'RADIAN_PER_HOUR'       => array(array('' => '1', '/' => '22619.467'), 'rad/h'),
        'RADIAN_PER_MINUTE'     => array(array('' => '1', '/' => '376.99112'), 'rad/m'),
        'RADIAN_PER_SECOND'     => array(array('' => '1', '/' => '6.2831853'), 'rad/s'),
        'REVOLUTION_PER_HOUR'   => array(array('' => '1', '/' => '3600'), 'rph'),
        'REVOLUTION_PER_MINUTE' => array(array('' => '1', '/' => '60'),   'rpm'),
        'REVOLUTION_PER_SECOND' => array('1',             'rps'),
        'RPM'                   => array(array('' => '1', '/' => '60'), 'rpm'),
        'TERRAHERTZ'            => array('1000000000000', 'THz'),
        'STANDARD'              =>'HERTZ'
    );
}
