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
 * Class for handling speed conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Speed
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Speed extends Zend_Measure_Abstract
{
    const STANDARD = 'METER_PER_SECOND';

    const BENZ                           = 'BENZ';
    const CENTIMETER_PER_DAY             = 'CENTIMETER_PER_DAY';
    const CENTIMETER_PER_HOUR            = 'CENTIMETER_PER_HOUR';
    const CENTIMETER_PER_MINUTE          = 'CENTIMETER_PER_MINUTE';
    const CENTIMETER_PER_SECOND          = 'CENTIMETER_PER_SECOND';
    const DEKAMETER_PER_DAY              = 'DEKAMETER_PER_DAY';
    const DEKAMETER_PER_HOUR             = 'DEKAMETER_PER_HOUR';
    const DEKAMETER_PER_MINUTE           = 'DEKAMETER_PER_MINUTE';
    const DEKAMETER_PER_SECOND           = 'DEKAMETER_PER_SECOND';
    const FOOT_PER_DAY                   = 'FOOT_PER_DAY';
    const FOOT_PER_HOUR                  = 'FOOT_PER_HOUR';
    const FOOT_PER_MINUTE                = 'FOOT_PER_MINUTE';
    const FOOT_PER_SECOND                = 'FOOT_PER_SECOND';
    const FURLONG_PER_DAY                = 'FURLONG_PER_DAY';
    const FURLONG_PER_FORTNIGHT          = 'FURLONG_PER_FORTNIGHT';
    const FURLONG_PER_HOUR               = 'FURLONG_PER_HOUR';
    const FURLONG_PER_MINUTE             = 'FURLONG_PER_MINUTE';
    const FURLONG_PER_SECOND             = 'FURLONG_PER_SECOND';
    const HECTOMETER_PER_DAY             = 'HECTOMETER_PER_DAY';
    const HECTOMETER_PER_HOUR            = 'HECTOMETER_PER_HOUR';
    const HECTOMETER_PER_MINUTE          = 'HECTOMETER_PER_MINUTE';
    const HECTOMETER_PER_SECOND          = 'HECTOMETER_PER_SECOND';
    const INCH_PER_DAY                   = 'INCH_PER_DAY';
    const INCH_PER_HOUR                  = 'INCH_PER_HOUR';
    const INCH_PER_MINUTE                = 'INCH_PER_MINUTE';
    const INCH_PER_SECOND                = 'INCH_PER_SECOND';
    const KILOMETER_PER_DAY              = 'KILOMETER_PER_DAY';
    const KILOMETER_PER_HOUR             = 'KILOMETER_PER_HOUR';
    const KILOMETER_PER_MINUTE           = 'KILOMETER_PER_MINUTE';
    const KILOMETER_PER_SECOND           = 'KILOMETER_PER_SECOND';
    const KNOT                           = 'KNOT';
    const LEAGUE_PER_DAY                 = 'LEAGUE_PER_DAY';
    const LEAGUE_PER_HOUR                = 'LEAGUE_PER_HOUR';
    const LEAGUE_PER_MINUTE              = 'LEAGUE_PER_MINUTE';
    const LEAGUE_PER_SECOND              = 'LEAGUE_PER_SECOND';
    const MACH                           = 'MACH';
    const MEGAMETER_PER_DAY              = 'MEGAMETER_PER_DAY';
    const MEGAMETER_PER_HOUR             = 'MEGAMETER_PER_HOUR';
    const MEGAMETER_PER_MINUTE           = 'MEGAMETER_PER_MINUTE';
    const MEGAMETER_PER_SECOND           = 'MEGAMETER_PER_SECOND';
    const METER_PER_DAY                  = 'METER_PER_DAY';
    const METER_PER_HOUR                 = 'METER_PER_HOUR';
    const METER_PER_MINUTE               = 'METER_PER_MINUTE';
    const METER_PER_SECOND               = 'METER_PER_SECOND';
    const MILE_PER_DAY                   = 'MILE_PER_DAY';
    const MILE_PER_HOUR                  = 'MILE_PER_HOUR';
    const MILE_PER_MINUTE                = 'MILE_PER_MINUTE';
    const MILE_PER_SECOND                = 'MILE_PER_SECOND';
    const MILLIMETER_PER_DAY             = 'MILLIMETER_PER_DAY';
    const MILLIMETER_PER_HOUR            = 'MILLIMETER_PER_HOUR';
    const MILLIMETER_PER_MINUTE          = 'MILLIMETER_PER_MINUTE';
    const MILLIMETER_PER_SECOND          = 'MILLIMETER_PER_SECOND';
    const MILLIMETER_PER_MICROSECOND     = 'MILLIMETER_PER_MICROSECOND';
    const MILLIMETER_PER_100_MICROSECOND = 'MILLIMETER_PER_100_MICROSECOND';
    const NAUTIC_MILE_PER_DAY            = 'NAUTIC_MILE_PER_DAY';
    const NAUTIC_MILE_PER_HOUR           = 'NAUTIC_MILE_PER_HOUR';
    const NAUTIC_MILE_PER_MINUTE         = 'NAUTIC_MILE_PER_MINUTE';
    const NAUTIC_MILE_PER_SECOND         = 'NAUTIC_MILE_PER_SECOND';
    const LIGHTSPEED_AIR                 = 'LIGHTSPEED_AIR';
    const LIGHTSPEED_GLASS               = 'LIGHTSPEED_GLASS';
    const LIGHTSPEED_ICE                 = 'LIGHTSPEED_ICE';
    const LIGHTSPEED_VACUUM              = 'LIGHTSPEED_VACUUM';
    const LIGHTSPEED_WATER               = 'LIGHTSPEED_WATER';
    const SOUNDSPEED_AIR                 = 'SOUNDSPEED_AIT';
    const SOUNDSPEED_METAL               = 'SOUNDSPEED_METAL';
    const SOUNDSPEED_WATER               = 'SOUNDSPEED_WATER';
    const YARD_PER_DAY                   = 'YARD_PER_DAY';
    const YARD_PER_HOUR                  = 'YARD_PER_HOUR';
    const YARD_PER_MINUTE                = 'YARD_PER_MINUTE';
    const YARD_PER_SECOND                = 'YARD_PER_SECOND';

    /**
     * Calculations for all speed units
     *
     * @var array
     */
    protected $_units = array(
        'BENZ'                           => array('1',                                     'Bz'),
        'CENTIMETER_PER_DAY'             => array(array('' => '0.01', '/' => '86400'),       'cm/day'),
        'CENTIMETER_PER_HOUR'            => array(array('' => '0.01', '/' => '3600'),        'cm/h'),
        'CENTIMETER_PER_MINUTE'          => array(array('' => '0.01', '/' => '60'),          'cm/m'),
        'CENTIMETER_PER_SECOND'          => array('0.01',                                  'cd/s'),
        'DEKAMETER_PER_DAY'              => array(array('' => '10', '/' => '86400'),         'dam/day'),
        'DEKAMETER_PER_HOUR'             => array(array('' => '10', '/' => '3600'),          'dam/h'),
        'DEKAMETER_PER_MINUTE'           => array(array('' => '10', '/' => '60'),            'dam/m'),
        'DEKAMETER_PER_SECOND'           => array('10',                                    'dam/s'),
        'FOOT_PER_DAY'                   => array(array('' => '0.3048', '/' => '86400'),     'ft/day'),
        'FOOT_PER_HOUR'                  => array(array('' => '0.3048', '/' => '3600'),      'ft/h'),
        'FOOT_PER_MINUTE'                => array(array('' => '0.3048', '/' => '60'),        'ft/m'),
        'FOOT_PER_SECOND'                => array('0.3048',                                'ft/s'),
        'FURLONG_PER_DAY'                => array(array('' => '201.1684', '/' => '86400'),   'fur/day'),
        'FURLONG_PER_FORTNIGHT'          => array(array('' => '201.1684', '/' => '1209600'), 'fur/fortnight'),
        'FURLONG_PER_HOUR'               => array(array('' => '201.1684', '/' => '3600'),    'fur/h'),
        'FURLONG_PER_MINUTE'             => array(array('' => '201.1684', '/' => '60'),      'fur/m'),
        'FURLONG_PER_SECOND'             => array('201.1684',                              'fur/s'),
        'HECTOMETER_PER_DAY'             => array(array('' => '100', '/' => '86400'),        'hm/day'),
        'HECTOMETER_PER_HOUR'            => array(array('' => '100', '/' => '3600'),         'hm/h'),
        'HECTOMETER_PER_MINUTE'          => array(array('' => '100', '/' => '60'),           'hm/m'),
        'HECTOMETER_PER_SECOND'          => array('100',                                   'hm/s'),
        'INCH_PER_DAY'                   => array(array('' => '0.0254', '/' => '86400'),     'in/day'),
        'INCH_PER_HOUR'                  => array(array('' => '0.0254', '/' => '3600'),      'in/h'),
        'INCH_PER_MINUTE'                => array(array('' => '0.0254', '/' => '60'),        'in/m'),
        'INCH_PER_SECOND'                => array('0.0254',                                'in/s'),
        'KILOMETER_PER_DAY'              => array(array('' => '1000', '/' => '86400'),       'km/day'),
        'KILOMETER_PER_HOUR'             => array(array('' => '1000', '/' => '3600'),        'km/h'),
        'KILOMETER_PER_MINUTE'           => array(array('' => '1000', '/' => '60'),          'km/m'),
        'KILOMETER_PER_SECOND'           => array('1000',                                  'km/s'),
        'KNOT'                           => array(array('' => '1852', '/' => '3600'),        'kn'),
        'LEAGUE_PER_DAY'                 => array(array('' => '4828.0417', '/' => '86400'),  'league/day'),
        'LEAGUE_PER_HOUR'                => array(array('' => '4828.0417', '/' => '3600'),   'league/h'),
        'LEAGUE_PER_MINUTE'              => array(array('' => '4828.0417', '/' => '60'),     'league/m'),
        'LEAGUE_PER_SECOND'              => array('4828.0417',                             'league/s'),
        'MACH'                           => array('340.29',                                'M'),
        'MEGAMETER_PER_DAY'              => array(array('' => '1000000', '/' => '86400'),    'Mm/day'),
        'MEGAMETER_PER_HOUR'             => array(array('' => '1000000', '/' => '3600'),     'Mm/h'),
        'MEGAMETER_PER_MINUTE'           => array(array('' => '1000000', '/' => '60'),       'Mm/m'),
        'MEGAMETER_PER_SECOND'           => array('1000000',                               'Mm/s'),
        'METER_PER_DAY'                  => array(array('' => '1', '/' => '86400'),          'm/day'),
        'METER_PER_HOUR'                 => array(array('' => '1', '/' => '3600'),           'm/h'),
        'METER_PER_MINUTE'               => array(array('' => '1', '/' => '60'),             'm/m'),
        'METER_PER_SECOND'               => array('1',                                     'm/s'),
        'MILE_PER_DAY'                   => array(array('' => '1609.344', '/' => '86400'),   'mi/day'),
        'MILE_PER_HOUR'                  => array(array('' => '1609.344', '/' => '3600'),    'mi/h'),
        'MILE_PER_MINUTE'                => array(array('' => '1609.344', '/' => '60'),      'mi/m'),
        'MILE_PER_SECOND'                => array('1609.344',                              'mi/s'),
        'MILLIMETER_PER_DAY'             => array(array('' => '0.001', '/' => '86400'),      'mm/day'),
        'MILLIMETER_PER_HOUR'            => array(array('' => '0.001', '/' => '3600'),       'mm/h'),
        'MILLIMETER_PER_MINUTE'          => array(array('' => '0.001', '/' => '60'),         'mm/m'),
        'MILLIMETER_PER_SECOND'          => array('0.001',                                 'mm/s'),
        'MILLIMETER_PER_MICROSECOND'     => array('1000',                                  'mm/µs'),
        'MILLIMETER_PER_100_MICROSECOND' => array('10',                                    'mm/100µs'),
        'NAUTIC_MILE_PER_DAY'            => array(array('' => '1852', '/' => '86400'),       'nmi/day'),
        'NAUTIC_MILE_PER_HOUR'           => array(array('' => '1852', '/' => '3600'),        'nmi/h'),
        'NAUTIC_MILE_PER_MINUTE'         => array(array('' => '1852', '/' => '60'),          'nmi/m'),
        'NAUTIC_MILE_PER_SECOND'         => array('1852',                                  'nmi/s'),
        'LIGHTSPEED_AIR'                 => array('299702547',                             'speed of light (air)'),
        'LIGHTSPEED_GLASS'               => array('199861638',                             'speed of light (glass)'),
        'LIGHTSPEED_ICE'                 => array('228849204',                             'speed of light (ice)'),
        'LIGHTSPEED_VACUUM'              => array('299792458',                             'speed of light (vacuum)'),
        'LIGHTSPEED_WATER'               => array('225407863',                             'speed of light (water)'),
        'SOUNDSPEED_AIT'                 => array('340.29',                                'speed of sound (air)'),
        'SOUNDSPEED_METAL'               => array('5000',                                  'speed of sound (metal)'),
        'SOUNDSPEED_WATER'               => array('1500',                                  'speed of sound (water)'),
        'YARD_PER_DAY'                   => array(array('' => '0.9144', '/' => '86400'),     'yd/day'),
        'YARD_PER_HOUR'                  => array(array('' => '0.9144', '/' => '3600'),      'yd/h'),
        'YARD_PER_MINUTE'                => array(array('' => '0.9144', '/' => '60'),        'yd/m'),
        'YARD_PER_SECOND'                => array('0.9144',                                'yd/s'),
        'STANDARD'                       => 'METER_PER_SECOND'
    );
}
