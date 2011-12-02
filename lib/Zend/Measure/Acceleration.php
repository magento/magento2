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
 * @version   $Id: Acceleration.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Implement needed classes
 */
#require_once 'Zend/Measure/Abstract.php';
#require_once 'Zend/Locale.php';

/**
 * Class for handling acceleration conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Acceleration
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Acceleration extends Zend_Measure_Abstract
{
    const STANDARD = 'METER_PER_SQUARE_SECOND';

    const CENTIGAL                     = 'CENTIGAL';
    const CENTIMETER_PER_SQUARE_SECOND = 'CENTIMETER_PER_SQUARE_SECOND';
    const DECIGAL                      = 'DECIGAL';
    const DECIMETER_PER_SQUARE_SECOND  = 'DECIMETER_PER_SQUARE_SECOND';
    const DEKAMETER_PER_SQUARE_SECOND  = 'DEKAMETER_PER_SQUARE_SECOND';
    const FOOT_PER_SQUARE_SECOND       = 'FOOT_PER_SQUARE_SECOND';
    const G                            = 'G';
    const GAL                          = 'GAL';
    const GALILEO                      = 'GALILEO';
    const GRAV                         = 'GRAV';
    const HECTOMETER_PER_SQUARE_SECOND = 'HECTOMETER_PER_SQUARE_SECOND';
    const INCH_PER_SQUARE_SECOND       = 'INCH_PER_SQUARE_SECOND';
    const KILOMETER_PER_HOUR_SECOND    = 'KILOMETER_PER_HOUR_SECOND';
    const KILOMETER_PER_SQUARE_SECOND  = 'KILOMETER_PER_SQUARE_SECOND';
    const METER_PER_SQUARE_SECOND      = 'METER_PER_SQUARE_SECOND';
    const MILE_PER_HOUR_MINUTE         = 'MILE_PER_HOUR_MINUTE';
    const MILE_PER_HOUR_SECOND         = 'MILE_PER_HOUR_SECOND';
    const MILE_PER_SQUARE_SECOND       = 'MILE_PER_SQUARE_SECOND';
    const MILLIGAL                     = 'MILLIGAL';
    const MILLIMETER_PER_SQUARE_SECOND = 'MILLIMETER_PER_SQUARE_SECOND';

    /**
     * Calculations for all acceleration units
     *
     * @var array
     */
    protected $_units = array(
        'CENTIGAL'                     => array('0.0001',   'cgal'),
        'CENTIMETER_PER_SQUARE_SECOND' => array('0.01',     'cm/s²'),
        'DECIGAL'                      => array('0.001',    'dgal'),
        'DECIMETER_PER_SQUARE_SECOND'  => array('0.1',      'dm/s²'),
        'DEKAMETER_PER_SQUARE_SECOND'  => array('10',       'dam/s²'),
        'FOOT_PER_SQUARE_SECOND'       => array('0.3048',   'ft/s²'),
        'G'                            => array('9.80665',  'g'),
        'GAL'                          => array('0.01',     'gal'),
        'GALILEO'                      => array('0.01',     'gal'),
        'GRAV'                         => array('9.80665',  'g'),
        'HECTOMETER_PER_SQUARE_SECOND' => array('100',      'h/s²'),
        'INCH_PER_SQUARE_SECOND'       => array('0.0254',   'in/s²'),
        'KILOMETER_PER_HOUR_SECOND'    => array(array('' => '5','/' => '18'), 'km/h²'),
        'KILOMETER_PER_SQUARE_SECOND'  => array('1000',     'km/s²'),
        'METER_PER_SQUARE_SECOND'      => array('1',        'm/s²'),
        'MILE_PER_HOUR_MINUTE'         => array(array('' => '22', '/' => '15', '*' => '0.3048', '/' => '60'), 'mph/m'),
        'MILE_PER_HOUR_SECOND'         => array(array('' => '22', '/' => '15', '*' => '0.3048'), 'mph/s'),
        'MILE_PER_SQUARE_SECOND'       => array('1609.344', 'mi/s²'),
        'MILLIGAL'                     => array('0.00001',  'mgal'),
        'MILLIMETER_PER_SQUARE_SECOND' => array('0.001',    'mm/s²'),
        'STANDARD'                     => 'METER_PER_SQUARE_SECOND'
    );
}
