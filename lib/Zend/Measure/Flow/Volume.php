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
 * @version   $Id: Volume.php 20096 2010-01-06 02:05:09Z bkarwin $
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
 * @subpackage Zend_Measure_Flow_Volume
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Flow_Volume extends Zend_Measure_Abstract
{
    const STANDARD = 'CUBIC_METER_PER_SECOND';

    const ACRE_FOOT_PER_DAY              = 'ACRE_FOOT_PER_DAY';
    const ACRE_FOOT_PER_HOUR             = 'ACRE_FOOT_PER_HOUR';
    const ACRE_FOOT_PER_MINUTE           = 'ACRE_FOOT_PER_MINUTE';
    const ACRE_FOOT_PER_SECOND           = 'ACRE_FOOT_PER_SECOND';
    const ACRE_FOOT_SURVEY_PER_DAY       = 'ACRE_FOOT_SURVEY_PER_DAY';
    const ACRE_FOOT_SURVEY_PER_HOUR      = 'ACRE_FOOT_SURVEY_PER_HOUR';
    const ACRE_FOOT_SURVEY_PER_MINUTE    = 'ACRE_FOOT_SURVEY_PER_MINUTE';
    const ACRE_FOOT_SURVEY_PER_SECOND    = 'ACRE_FOOT_SURVEY_PER_SECOND';
    const ACRE_INCH_PER_DAY              = 'ACRE_INCH_PER_DAY';
    const ACRE_INCH_PER_HOUR             = 'ACRE_INCH_PER_HOUR';
    const ACRE_INCH_PER_MINUTE           = 'ACRE_INCH_PER_MINUTE';
    const ACRE_INCH_PER_SECOND           = 'ACRE_INCH_PER_SECOND';
    const ACRE_INCH_SURVEY_PER_DAY       = 'ACRE_INCH_SURVEY_PER_DAY';
    const ACRE_INCH_SURVEY_PER_HOUR      = 'ACRE_INCH_SURVEY_PER_HOUR';
    const ACRE_INCH_SURVEY_PER_MINUTE    = 'ACRE_INCH_SURVEY_PER_MINUTE';
    const ACRE_INCH_SURVEY_PER_SECOND    = 'ACRE_INCH_SURVEY_PER_SECOND';
    const BARREL_PETROLEUM_PER_DAY       = 'BARREL_PETROLEUM_PER_DAY';
    const BARREL_PETROLEUM_PER_HOUR      = 'BARREL_PETROLEUM_PER_HOUR';
    const BARREL_PETROLEUM_PER_MINUTE    = 'BARREL_PETROLEUM_PER_MINUTE';
    const BARREL_PETROLEUM_PER_SECOND    = 'BARREL_PETROLEUM_PER_SECOND';
    const BARREL_PER_DAY                 = 'BARREL_PER_DAY';
    const BARREL_PER_HOUR                = 'BARREL_PER_HOUR';
    const BARREL_PER_MINUTE              = 'BARREL_PER_MINUTE';
    const BARREL_PER_SECOND              = 'BARREL_PER_SECOND';
    const BARREL_US_PER_DAY              = 'BARREL_US_PER_DAY';
    const BARREL_US_PER_HOUR             = 'BARREL_US_PER_HOUR';
    const BARREL_US_PER_MINUTE           = 'BARREL_US_PER_MINUTE';
    const BARREL_US_PER_SECOND           = 'BARREL_US_PER_SECOND';
    const BARREL_WINE_PER_DAY            = 'BARREL_WINE_PER_DAY';
    const BARREL_WINE_PER_HOUR           = 'BARREL_WINE_PER_HOUR';
    const BARREL_WINE_PER_MINUTE         = 'BARREL_WINE_PER_MINUTE';
    const BARREL_WINE_PER_SECOND         = 'BARREL_WINE_PER_SECOND';
    const BARREL_BEER_PER_DAY            = 'BARREL_BEER_PER_DAY';
    const BARREL_BEER_PER_HOUR           = 'BARREL_BEER_PER_HOUR';
    const BARREL_BEER_PER_MINUTE         = 'BARREL_BEER_PER_MINUTE';
    const BARREL_BEER_PER_SECOND         = 'BARREL_BEER_PER_SECOND';
    const BILLION_CUBIC_FOOT_PER_DAY     = 'BILLION_CUBIC_FOOT_PER_DAY';
    const BILLION_CUBIC_FOOT_PER_HOUR    = 'BILLION_CUBIC_FOOT_PER_HOUR';
    const BILLION_CUBIC_FOOT_PER_MINUTE  = 'BILLION_CUBIC_FOOT_PER_MINUTE';
    const BILLION_CUBIC_FOOT_PER_SECOND  = 'BILLION_CUBIC_FOOT_PER_SECOND';
    const CENTILITER_PER_DAY             = 'CENTILITER_PER_DAY';
    const CENTILITER_PER_HOUR            = 'CENTILITER_PER_HOUR';
    const CENTILITER_PER_MINUTE          = 'CENTILITER_PER_MINUTE';
    const CENTILITER_PER_SECOND          = 'CENTILITER_PER_SECOND';
    const CUBEM_PER_DAY                  = 'CUBEM_PER_DAY';
    const CUBEM_PER_HOUR                 = 'CUBEM_PER_HOUR';
    const CUBEM_PER_MINUTE               = 'CUBEM_PER_MINUTE';
    const CUBEM_PER_SECOND               = 'CUBEM_PER_SECOND';
    const CUBIC_CENTIMETER_PER_DAY       = 'CUBIC_CENTIMETER_PER_DAY';
    const CUBIC_CENTIMETER_PER_HOUR      = 'CUBIC_CENTIMETER_PER_HOUR';
    const CUBIC_CENTIMETER_PER_MINUTE    = 'CUBIC_CENTIMETER_PER_MINUTE';
    const CUBIC_CENTIMETER_PER_SECOND    = 'CUBIC_CENTIMETER_PER_SECOND';
    const CUBIC_DECIMETER_PER_DAY        = 'CUBIC_DECIMETER_PER_DAY';
    const CUBIC_DECIMETER_PER_HOUR       = 'CUBIC_DECIMETER_PER_HOUR';
    const CUBIC_DECIMETER_PER_MINUTE     = 'CUBIC_DECIMETER_PER_MINUTE';
    const CUBIC_DECIMETER_PER_SECOND     = 'CUBIC_DECIMETER_PER_SECOND';
    const CUBIC_DEKAMETER_PER_DAY        = 'CUBIC_DEKAMETER_PER_DAY';
    const CUBIC_DEKAMETER_PER_HOUR       = 'CUBIC_DEKAMETER_PER_HOUR';
    const CUBIC_DEKAMETER_PER_MINUTE     = 'CUBIC_DEKAMETER_PER_MINUTE';
    const CUBIC_DEKAMETER_PER_SECOND     = 'CUBIC_DEKAMETER_PER_SECOND';
    const CUBIC_FOOT_PER_DAY             = 'CUBIC_FOOT_PER_DAY';
    const CUBIC_FOOT_PER_HOUR            = 'CUBIC_FOOT_PER_HOUR';
    const CUBIC_FOOT_PER_MINUTE          = 'CUBIC_FOOT_PER_MINUTE';
    const CUBIC_FOOT_PER_SECOND          = 'CUBIC_FOOT_PER_SECOND';
    const CUBIC_INCH_PER_DAY             = 'CUBIC_INCH_PER_DAY';
    const CUBIC_INCH_PER_HOUR            = 'CUBIC_INCH_PER_HOUR';
    const CUBIC_INCH_PER_MINUTE          = 'CUBIC_INCH_PER_MINUTE';
    const CUBIC_INCH_PER_SECOND          = 'CUBIC_INCH_PER_SECOND';
    const CUBIC_KILOMETER_PER_DAY        = 'CUBIC_KILOMETER_PER_DAY';
    const CUBIC_KILOMETER_PER_HOUR       = 'CUBIC_KILOMETER_PER_HOUR';
    const CUBIC_KILOMETER_PER_MINUTE     = 'CUBIC_KILOMETER_PER_MINUTE';
    const CUBIC_KILOMETER_PER_SECOND     = 'CUBIC_KILOMETER_PER_SECOND';
    const CUBIC_METER_PER_DAY            = 'CUBIC_METER_PER_DAY';
    const CUBIC_METER_PER_HOUR           = 'CUBIC_METER_PER_HOUR';
    const CUBIC_METER_PER_MINUTE         = 'CUBIC_METER_PER_MINUTE';
    const CUBIC_METER_PER_SECOND         = 'CUBIC_METER_PER_SECOND';
    const CUBIC_MILE_PER_DAY             = 'CUBIC_MILE_PER_DAY';
    const CUBIC_MILE_PER_HOUR            = 'CUBIC_MILE_PER_HOUR';
    const CUBIC_MILE_PER_MINUTE          = 'CUBIC_MILE_PER_MINUTE';
    const CUBIC_MILE_PER_SECOND          = 'CUBIC_MILE_PER_SECOND';
    const CUBIC_MILLIMETER_PER_DAY       = 'CUBIC_MILLIMETER_PER_DAY';
    const CUBIC_MILLIMETER_PER_HOUR      = 'CUBIC_MILLIMETER_PER_HOUR';
    const CUBIC_MILLIMETER_PER_MINUTE    = 'CUBIC_MILLIMETER_PER_MINUTE';
    const CUBIC_MILLIMETER_PER_SECOND    = 'CUBIC_MILLIMETER_PER_SECOND';
    const CUBIC_YARD_PER_DAY             = 'CUBIC_YARD_PER_DAY';
    const CUBIC_YARD_PER_HOUR            = 'CUBIC_YARD_PER_HOUR';
    const CUBIC_YARD_PER_MINUTE          = 'CUBIC_YARD_PER_MINUTE';
    const CUBIC_YARD_PER_SECOND          = 'CUBIC_YARD_PER_SECOND';
    const CUSEC                          = 'CUSEC';
    const DECILITER_PER_DAY              = 'DECILITER_PER_DAY';
    const DECILITER_PER_HOUR             = 'DECILITER_PER_HOUR';
    const DECILITER_PER_MINUTE           = 'DECILITER_PER_MINUTE';
    const DECILITER_PER_SECOND           = 'DECILITER_PER_SECOND';
    const DEKALITER_PER_DAY              = 'DEKALITER_PER_DAY';
    const DEKALITER_PER_HOUR             = 'DEKALITER_PER_HOUR';
    const DEKALITER_PER_MINUTE           = 'DEKALITER_PER_MINUTE';
    const DEKALITER_PER_SECOND           = 'DEKALITER_PER_SECOND';
    const GALLON_PER_DAY                 = 'GALLON_PER_DAY';
    const GALLON_PER_HOUR                = 'GALLON_PER_HOUR';
    const GALLON_PER_MINUTE              = 'GALLON_PER_MINUTE';
    const GALLON_PER_SECOND              = 'GALLON_PER_SECOND';
    const GALLON_US_PER_DAY              = 'GALLON_US_PER_DAY';
    const GALLON_US_PER_HOUR             = 'GALLON_US_PER_HOUR';
    const GALLON_US_PER_MINUTE           = 'GALLON_US_PER_MINUTE';
    const GALLON_US_PER_SECOND           = 'GALLON_US_PER_SECOND';
    const HECTARE_METER_PER_DAY          = 'HECTARE_METER_PER_DAY';
    const HECTARE_METER_PER_HOUR         = 'HECTARE_METER_PER_HOUR';
    const HECTARE_METER_PER_MINUTE       = 'HECTARE_METER_PER_MINUTE';
    const HECTARE_METER_PER_SECOND       = 'HECTARE_METER_PER_SECOND';
    const HECTOLITER_PER_DAY             = 'HECTOLITER_PER_DAY';
    const HECTOLITER_PER_HOUR            = 'HECTOLITER_PER_HOUR';
    const HECTOLITER_PER_MINUTE          = 'HECTOLITER_PER_MINUTE';
    const HECTOLITER_PER_SECOND          = 'HECTOLITER_PER_SECOND';
    const KILOLITER_PER_DAY              = 'KILOLITER_PER_DAY';
    const KILOLITER_PER_HOUR             = 'KILOLITER_PER_HOUR';
    const KILOLITER_PER_MINUTE           = 'KILOLITER_PER_MINUTE';
    const KILOLITER_PER_SECOND           = 'KILOLITER_PER_SECOND';
    const LAMBDA_PER_DAY                 = 'LAMBDA_PER_DAY';
    const LAMBDA_PER_HOUR                = 'LAMBDA_PER_HOUR';
    const LAMBDA_PER_MINUTE              = 'LAMBDA_PER_MINUTE';
    const LAMBDA_PER_SECOND              = 'LAMBDA_PER_SECOND';
    const LITER_PER_DAY                  = 'LITER_PER_DAY';
    const LITER_PER_HOUR                 = 'LITER_PER_HOUR';
    const LITER_PER_MINUTE               = 'LITER_PER_MINUTE';
    const LITER_PER_SECOND               = 'LITER_PER_SECOND';
    const MILLILITER_PER_DAY             = 'MILLILITER_PER_DAY';
    const MILLILITER_PER_HOUR            = 'MILLILITER_PER_HOUR';
    const MILLILITER_PER_MINUTE          = 'MILLILITER_PER_MINUTE';
    const MILLILITER_PER_SECOND          = 'MILLILITER_PER_SECOND';
    const MILLION_ACRE_FOOT_PER_DAY      = 'MILLION_ACRE_FOOT_PER_DAY';
    const MILLION_ACRE_FOOT_PER_HOUR     = 'MILLION_ACRE_FOOT_PER_HOUR';
    const MILLION_ACRE_FOOT_PER_MINUTE   = 'MILLION_ACRE_FOOT_PER_MINUTE';
    const MILLION_ACRE_FOOT_PER_SECOND   = 'MILLION_ACRE_FOOT_PER_SECOND';
    const MILLION_CUBIC_FOOT_PER_DAY     = 'MILLION_CUBIC_FOOT_PER_DAY';
    const MILLION_CUBIC_FOOT_PER_HOUR    = 'MILLION_CUBIC_FOOT_PER_HOUR';
    const MILLION_CUBIC_FOOT_PER_MINUTE  = 'MILLION_CUBIC_FOOT_PER_MINUTE';
    const MILLION_CUBIC_FOOT_PER_SECOND  = 'MILLION_CUBIC_FOOT_PER_SECOND';
    const MILLION_GALLON_PER_DAY         = 'MILLION_GALLON_PER_DAY';
    const MILLION_GALLON_PER_HOUR        = 'MILLION_GALLON_PER_HOUR';
    const MILLION_GALLON_PER_MINUTE      = 'MILLION_GALLON_PER_MINUTE';
    const MILLION_GALLON_PER_SECOND      = 'MILLION_GALLON_PER_SECOND';
    const MILLION_GALLON_US_PER_DAY      = 'MILLION_GALLON_US_PER_DAY';
    const MILLION_GALLON_US_PER_HOUR     = 'MILLION_GALLON_US_PER_HOUR';
    const MILLION_GALLON_US_PER_MINUTE   = 'MILLION_GALLON_US_PER_MINUTE';
    const MILLION_GALLON_US_PER_SECOND   = 'MILLION_GALLON_US_PER_SECOND';
    const MINERS_INCH_AZ                 = 'MINERS_INCH_AZ';
    const MINERS_INCH_CA                 = 'MINERS_INCH_CA';
    const MINERS_INCH_OR                 = 'MINERS_INCH_OR';
    const MINERS_INCH_CO                 = 'MINERS_INCH_CO';
    const MINERS_INCH_ID                 = 'MINERS_INCH_ID';
    const MINERS_INCH_WA                 = 'MINERS_INCH_WA';
    const MINERS_INCH_NM                 = 'MINERS_INCH_NM';
    const OUNCE_PER_DAY                  = 'OUNCE_PER_DAY';
    const OUNCE_PER_HOUR                 = 'OUNCE_PER_HOUR';
    const OUNCE_PER_MINUTE               = 'OUNCE_PER_MINUTE';
    const OUNCE_PER_SECOND               = 'OUNCE_PER_SECOND';
    const OUNCE_US_PER_DAY               = 'OUNCE_US_PER_DAY';
    const OUNCE_US_PER_HOUR              = 'OUNCE_US_PER_HOUR';
    const OUNCE_US_PER_MINUTE            = 'OUNCE_US_PER_MINUTE';
    const OUNCE_US_PER_SECOND            = 'OUNCE_US_PER_SECOND';
    const PETROGRAD_STANDARD_PER_DAY     = 'PETROGRAD_STANDARD_PER_DAY';
    const PETROGRAD_STANDARD_PER_HOUR    = 'PETROGRAD_STANDARD_PER_HOUR';
    const PETROGRAD_STANDARD_PER_MINUTE  = 'PETROGRAD_STANDARD_PER_MINUTE';
    const PETROGRAD_STANDARD_PER_SECOND  = 'PETROGRAD_STANDARD_PER_SECOND';
    const STERE_PER_DAY                  = 'STERE_PER_DAY';
    const STERE_PER_HOUR                 = 'STERE_PER_HOUR';
    const STERE_PER_MINUTE               = 'STERE_PER_MINUTE';
    const STERE_PER_SECOND               = 'STERE_PER_SECOND';
    const THOUSAND_CUBIC_FOOT_PER_DAY    = 'THOUSAND_CUBIC_FOOT_PER_DAY';
    const THOUSAND_CUBIC_FOOT_PER_HOUR   = 'THOUSAND_CUBIC_FOOT_PER_HOUR';
    const THOUSAND_CUBIC_FOOT_PER_MINUTE = 'THOUSAND_CUBIC_FOOT_PER_MINUTE';
    const THOUSAND_CUBIC_FOOT_PER_SECOND = 'THOUSAND_CUBIC_FOOT_PER_SECOND';
    const TRILLION_CUBIC_FOOT_PER_DAY    = 'TRILLION_CUBIC_FOOT_PER_DAY';
    const TRILLION_CUBIC_FOOT_PER_HOUR   = 'TRILLION_CUBIC_FOOT_PER_HOUR';
    const TRILLION_CUBIC_FOOT_PER_MINUTE = 'TRILLION_CUBIC_FOOT_PER_MINUTE';
    const TRILLION_CUBIC_FOOT_PER_SECOND = 'TRILLION_CUBIC_FOOT_PER_';

    /**
     * Calculations for all flow volume units
     *
     * @var array
     */
    protected $_units = array(
        'ACRE_FOOT_PER_DAY'           => array(array('' => '1233.48184', '/' => '86400'),      'ac ft/day'),
        'ACRE_FOOT_PER_HOUR'          => array(array('' => '1233.48184', '/' => '3600'),       'ac ft/h'),
        'ACRE_FOOT_PER_MINUTE'        => array(array('' => '1233.48184', '/' => '60'),         'ac ft/m'),
        'ACRE_FOOT_PER_SECOND'        => array('1233.48184',                                 'ac ft/s'),
        'ACRE_FOOT_SURVEY_PER_DAY'    => array(array('' => '1233.48924', '/' => '86400'),      'ac ft/day'),
        'ACRE_FOOT_SURVEY_PER_HOUR'   => array(array('' => '1233.48924', '/' => '3600'),       'ac ft/h'),
        'ACRE_FOOT_SURVEY_PER_MINUTE' => array(array('' => '1233.48924', '/' => '60'),         'ac ft/m'),
        'ACRE_FOOT_SURVEY_PER_SECOND' => array('1233.48924',                                 'ac ft/s'),
        'ACRE_INCH_PER_DAY'           => array(array('' => '1233.48184', '/' => '1036800'),    'ac in/day'),
        'ACRE_INCH_PER_HOUR'          => array(array('' => '1233.48184', '/' => '43200'),      'ac in/h'),
        'ACRE_INCH_PER_MINUTE'        => array(array('' => '1233.48184', '/' => '720'),        'ac in/m'),
        'ACRE_INCH_PER_SECOND'        => array(array('' => '1233.48184', '/' => '12'),         'ac in/s'),
        'ACRE_INCH_SURVEY_PER_DAY'    => array(array('' => '1233.48924', '/' => '1036800'),    'ac in/day'),
        'ACRE_INCH_SURVEY_PER_HOUR'   => array(array('' => '1233.48924', '/' => '43200'),      'ac in/h'),
        'ACRE_INCH_SURVEY_PER_MINUTE' => array(array('' => '1233.48924', '/' => '720'),        'ac in /m'),
        'ACRE_INCH_SURVEY_PER_SECOND' => array(array('' => '1233.48924', '/' => '12'),         'ac in/s'),
        'BARREL_PETROLEUM_PER_DAY'    => array(array('' => '0.1589872956', '/' => '86400'),    'bbl/day'),
        'BARREL_PETROLEUM_PER_HOUR'   => array(array('' => '0.1589872956', '/' => '3600'),     'bbl/h'),
        'BARREL_PETROLEUM_PER_MINUTE' => array(array('' => '0.1589872956', '/' => '60'),       'bbl/m'),
        'BARREL_PETROLEUM_PER_SECOND' => array('0.1589872956',                               'bbl/s'),
        'BARREL_PER_DAY'              => array(array('' => '0.16365924', '/' => '86400'),      'bbl/day'),
        'BARREL_PER_HOUR'             => array(array('' => '0.16365924', '/' => '3600'),       'bbl/h'),
        'BARREL_PER_MINUTE'           => array(array('' => '0.16365924', '/' => '60'),         'bbl/m'),
        'BARREL_PER_SECOND'           => array('0.16365924',                                 'bbl/s'),
        'BARREL_US_PER_DAY'           => array(array('' => '0.1192404717', '/' => '86400'),    'bbl/day'),
        'BARREL_US_PER_HOUR'          => array(array('' => '0.1192404717', '/' => '3600'),     'bbl/h'),
        'BARREL_US_PER_MINUTE'        => array(array('' => '0.1192404717', '/' => '60'),       'bbl/m'),
        'BARREL_US_PER_SECOND'        => array('0.1192404717',                               'bbl/s'),
        'BARREL_WINE_PER_DAY'         => array(array('' => '0.1173477658', '/' => '86400'),    'bbl/day'),
        'BARREL_WINE_PER_HOUR'        => array(array('' => '0.1173477658', '/' => '3600'),     'bbl/h'),
        'BARREL_WINE_PER_MINUTE'      => array(array('' => '0.1173477658', '/' => '60'),       'bbl/m'),
        'BARREL_WINE_PER_SECOND'      => array('0.1173477658',                               'bbl/s'),
        'BARREL_BEER_PER_DAY'         => array(array('' => '0.1173477658', '/' => '86400'),    'bbl/day'),
        'BARREL_BEER_PER_HOUR'        => array(array('' => '0.1173477658', '/' => '3600'),     'bbl/h'),
        'BARREL_BEER_PER_MINUTE'      => array(array('' => '0.1173477658', '/' => '60'),       'bbl/m'),
        'BARREL_BEER_PER_SECOND'      => array('0.1173477658',                               'bbl/s'),
        'BILLION_CUBIC_FOOT_PER_DAY'  => array(array('' => '28316847', '/' => '86400'),        'bn ft³/day'),
        'BILLION_CUBIC_FOOT_PER_HOUR' => array(array('' => '28316847', '/' => '3600'),         'bn ft³/h'),
        'BILLION_CUBIC_FOOT_PER_MINUTE' => array(array('' => '28316847', '/' => '60'),         'bn ft³/m'),
        'BILLION_CUBIC_FOOT_PER_SECOND' => array('28316847',                                 'bn ft³/s'),
        'CENTILITER_PER_DAY'          => array(array('' => '0.00001', '/' => '86400'),         'cl/day'),
        'CENTILITER_PER_HOUR'         => array(array('' => '0.00001', '/' => '3600'),          'cl/h'),
        'CENTILITER_PER_MINUTE'       => array(array('' => '0.00001', '/' => '60'),            'cl/m'),
        'CENTILITER_PER_SECOND'       => array('0.00001',                                    'cl/s'),
        'CUBEM_PER_DAY'               => array(array('' => '4168181830', '/' => '86400'),      'cubem/day'),
        'CUBEM_PER_HOUR'              => array(array('' => '4168181830', '/' => '3600'),       'cubem/h'),
        'CUBEM_PER_MINUTE'            => array(array('' => '4168181830', '/' => '60'),         'cubem/m'),
        'CUBEM_PER_SECOND'            => array('4168181830',                                 'cubem/s'),
        'CUBIC_CENTIMETER_PER_DAY'    => array(array('' => '0.000001', '/' => '86400'),        'cm³/day'),
        'CUBIC_CENTIMETER_PER_HOUR'   => array(array('' => '0.000001', '/' => '3600'),         'cm³/h'),
        'CUBIC_CENTIMETER_PER_MINUTE' => array(array('' => '0.000001', '/' => '60'),           'cm³/m'),
        'CUBIC_CENTIMETER_PER_SECOND' => array('0.000001',                                   'cm³/s'),
        'CUBIC_DECIMETER_PER_DAY'     => array(array('' => '0.001', '/' => '86400'),           'dm³/day'),
        'CUBIC_DECIMETER_PER_HOUR'    => array(array('' => '0.001', '/' => '3600'),            'dm³/h'),
        'CUBIC_DECIMETER_PER_MINUTE'  => array(array('' => '0.001', '/' => '60'),              'dm³/m'),
        'CUBIC_DECIMETER_PER_SECOND'  => array('0.001',                                      'dm³/s'),
        'CUBIC_DEKAMETER_PER_DAY'     => array(array('' => '1000', '/' => '86400'),            'dam³/day'),
        'CUBIC_DEKAMETER_PER_HOUR'    => array(array('' => '1000', '/' => '3600'),             'dam³/h'),
        'CUBIC_DEKAMETER_PER_MINUTE'  => array(array('' => '1000', '/' => '60'),               'dam³/m'),
        'CUBIC_DEKAMETER_PER_SECOND'  => array('1000',                                       'dam³/s'),
        'CUBIC_FOOT_PER_DAY'          => array(array('' => '0.028316847', '/' => '86400'),     'ft³/day'),
        'CUBIC_FOOT_PER_HOUR'         => array(array('' => '0.028316847', '/' => '3600'),      'ft³/h'),
        'CUBIC_FOOT_PER_MINUTE'       => array(array('' => '0.028316847', '/' => '60'),        'ft³/m'),
        'CUBIC_FOOT_PER_SECOND'       => array('0.028316847',                                'ft³/s'),
        'CUBIC_INCH_PER_DAY'          => array(array('' => '0.028316847', '/' => '149299200'), 'in³/day'),
        'CUBIC_INCH_PER_HOUR'         => array(array('' => '0.028316847', '/' => '6220800'),   'in³/h'),
        'CUBIC_INCH_PER_MINUTE'       => array(array('' => '0.028316847', '/' => '103680'),    'in³/m'),
        'CUBIC_INCH_PER_SECOND'       => array('0.028316847',                                'in³/s'),
        'CUBIC_KILOMETER_PER_DAY'     => array(array('' => '1000000000', '/' => '86400'),      'km³/day'),
        'CUBIC_KILOMETER_PER_HOUR'    => array(array('' => '1000000000', '/' => '3600'),       'km³/h'),
        'CUBIC_KILOMETER_PER_MINUTE'  => array(array('' => '1000000000', '/' => '60'),         'km³/m'),
        'CUBIC_KILOMETER_PER_SECOND'  => array('1000000000',                                 'km³/s'),
        'CUBIC_METER_PER_DAY'         => array(array('' => '1', '/' => '86400'),               'm³/day'),
        'CUBIC_METER_PER_HOUR'        => array(array('' => '1', '/' => '3600'),                'm³/h'),
        'CUBIC_METER_PER_MINUTE'      => array(array('' => '1', '/' => '60'),                  'm³/m'),
        'CUBIC_METER_PER_SECOND'      => array('1',                                          'm³/s'),
        'CUBIC_MILE_PER_DAY'          => array(array('' => '4168181830', '/' => '86400'),      'mi³/day'),
        'CUBIC_MILE_PER_HOUR'         => array(array('' => '4168181830', '/' => '3600'),       'mi³/h'),
        'CUBIC_MILE_PER_MINUTE'       => array(array('' => '4168181830', '/' => '60'),         'mi³/m'),
        'CUBIC_MILE_PER_SECOND'       => array('4168181830',                                 'mi³/s'),
        'CUBIC_MILLIMETER_PER_DAY'    => array(array('' => '0.000000001', '/' => '86400'),     'mm³/day'),
        'CUBIC_MILLIMETER_PER_HOUR'   => array(array('' => '0.000000001', '/' => '3600'),      'mm³/h'),
        'CUBIC_MILLIMETER_PER_MINUTE' => array(array('' => '0.000000001', '/' => '60'),        'mm³/m'),
        'CUBIC_MILLIMETER_PER_SECOND' => array('0.000000001',                                'mm³/s'),
        'CUBIC_YARD_PER_DAY'          => array(array('' => '0.764554869', '/' => '86400'),     'yd³/day'),
        'CUBIC_YARD_PER_HOUR'         => array(array('' => '0.764554869', '/' => '3600'),      'yd³/h'),
        'CUBIC_YARD_PER_MINUTE'       => array(array('' => '0.764554869', '/' => '60'),        'yd³/m'),
        'CUBIC_YARD_PER_SECOND'       => array('0.764554869',                                'yd³/s'),
        'CUSEC'                       => array('0.028316847',                                'cusec'),
        'DECILITER_PER_DAY'           => array(array('' => '0.0001', '/' => '86400'),          'dl/day'),
        'DECILITER_PER_HOUR'          => array(array('' => '0.0001', '/' => '3600'),           'dl/h'),
        'DECILITER_PER_MINUTE'        => array(array('' => '0.0001', '/' => '60'),             'dl/m'),
        'DECILITER_PER_SECOND'        => array('0.0001',                                     'dl/s'),
        'DEKALITER_PER_DAY'           => array(array('' => '0.01', '/' => '86400'),            'dal/day'),
        'DEKALITER_PER_HOUR'          => array(array('' => '0.01', '/' => '3600'),             'dal/h'),
        'DEKALITER_PER_MINUTE'        => array(array('' => '0.01', '/' => '60'),               'dal/m'),
        'DEKALITER_PER_SECOND'        => array('0.01',                                       'dal/s'),
        'GALLON_PER_DAY'              => array(array('' => '0.00454609', '/' => '86400'),      'gal/day'),
        'GALLON_PER_HOUR'             => array(array('' => '0.00454609', '/' => '3600'),       'gal/h'),
        'GALLON_PER_MINUTE'           => array(array('' => '0.00454609', '/' => '60'),         'gal/m'),
        'GALLON_PER_SECOND'           => array('0.00454609',                                 'gal/s'),
        'GALLON_US_PER_DAY'           => array(array('' => '0.0037854118', '/' => '86400'),    'gal/day'),
        'GALLON_US_PER_HOUR'          => array(array('' => '0.0037854118', '/' => '3600'),     'gal/h'),
        'GALLON_US_PER_MINUTE'        => array(array('' => '0.0037854118', '/' => '60'),       'gal/m'),
        'GALLON_US_PER_SECOND'        => array('0.0037854118',                               'gal/s'),
        'HECTARE_METER_PER_DAY'       => array(array('' => '10000', '/' => '86400'),           'ha m/day'),
        'HECTARE_METER_PER_HOUR'      => array(array('' => '10000', '/' => '3600'),            'ha m/h'),
        'HECTARE_METER_PER_MINUTE'    => array(array('' => '10000', '/' => '60'),              'ha m/m'),
        'HECTARE_METER_PER_SECOND'    => array('10000',                                      'ha m/s'),
        'HECTOLITER_PER_DAY'          => array(array('' => '0.1', '/' => '86400'),             'hl/day'),
        'HECTOLITER_PER_HOUR'         => array(array('' => '0.1', '/' => '3600'),              'hl/h'),
        'HECTOLITER_PER_MINUTE'       => array(array('' => '0.1', '/' => '60'),                'hl/m'),
        'HECTOLITER_PER_SECOND'       => array('0.1',                                        'hl/s'),
        'KILOLITER_PER_DAY'           => array(array('' => '1', '/' => '86400'),               'kl/day'),
        'KILOLITER_PER_HOUR'          => array(array('' => '1', '/' => '3600'),                'kl/h'),
        'KILOLITER_PER_MINUTE'        => array(array('' => '1', '/' => '60'),                  'kl/m'),
        'KILOLITER_PER_SECOND'        => array('1',                                          'kl/s'),
        'LAMBDA_PER_DAY'              => array(array('' => '0.000000001', '/' => '86400'),     'λ/day'),
        'LAMBDA_PER_HOUR'             => array(array('' => '0.000000001', '/' => '3600'),      'λ/h'),
        'LAMBDA_PER_MINUTE'           => array(array('' => '0.000000001', '/' => '60'),        'λ/m'),
        'LAMBDA_PER_SECOND'           => array('0.000000001',                                'λ/s'),
        'LITER_PER_DAY'               => array(array('' => '0.001', '/' => '86400'),           'l/day'),
        'LITER_PER_HOUR'              => array(array('' => '0.001', '/' => '3600'),            'l/h'),
        'LITER_PER_MINUTE'            => array(array('' => '0.001', '/' => '60'),              'l/m'),
        'LITER_PER_SECOND'            => array('0.001',                                      'l/s'),
        'MILLILITER_PER_DAY'          => array(array('' => '0.000001', '/' => '86400'),        'ml/day'),
        'MILLILITER_PER_HOUR'         => array(array('' => '0.000001', '/' => '3600'),         'ml/h'),
        'MILLILITER_PER_MINUTE'       => array(array('' => '0.000001', '/' => '60'),           'ml/m'),
        'MILLILITER_PER_SECOND'       => array('0.000001',                                   'ml/s'),
        'MILLION_ACRE_FOOT_PER_DAY'   => array(array('' => '1233481840', '/' => '86400'),      'million ac ft/day'),
        'MILLION_ACRE_FOOT_PER_HOUR'  => array(array('' => '1233481840', '/' => '3600'),       'million ac ft/h'),
        'MILLION_ACRE_FOOT_PER_MINUTE'  => array(array('' => '1233481840', '/' => '60'),       'million ac ft/m'),
        'MILLION_ACRE_FOOT_PER_SECOND'  => array('1233481840',                               'million ac ft/s'),
        'MILLION_CUBIC_FOOT_PER_DAY'    => array(array('' => '28316.847', '/' => '86400'),     'million ft³/day'),
        'MILLION_CUBIC_FOOT_PER_HOUR'   => array(array('' => '28316.847', '/' => '3600'),      'million ft³/h'),
        'MILLION_CUBIC_FOOT_PER_MINUTE' => array(array('' => '28316.847', '/' => '60'),        'million ft³/m'),
        'MILLION_CUBIC_FOOT_PER_SECOND' => array('28316.847',                                'million ft³/s'),
        'MILLION_GALLON_PER_DAY'      => array(array('' => '4546.09', '/' => '86400'),         'million gal/day'),
        'MILLION_GALLON_PER_HOUR'     => array(array('' => '4546.09', '/' => '3600'),          'million gal/h'),
        'MILLION_GALLON_PER_MINUTE'   => array(array('' => '4546.09', '/' => '60'),            'million gal/m'),
        'MILLION_GALLON_PER_SECOND'   => array('4546.09',                                    'million gal/s'),
        'MILLION_GALLON_US_PER_DAY'   => array(array('' => '3785.4118', '/' => '86400'),       'million gal/day'),
        'MILLION_GALLON_US_PER_HOUR'  => array(array('' => '3785.4118', '/' => '3600'),        'million gal/h'),
        'MILLION_GALLON_US_PER_MINUTE'=> array(array('' => '3785.4118', '/' => '60'),          'million gal/m'),
        'MILLION_GALLON_US_PER_SECOND'=> array('3785.4118',                                  'million gal/s'),
        'MINERS_INCH_AZ'              => array(array('' => '0.0424752705', '/' => '60'),       "miner's inch"),
        'MINERS_INCH_CA'              => array(array('' => '0.0424752705', '/' => '60'),       "miner's inch"),
        'MINERS_INCH_OR'              => array(array('' => '0.0424752705', '/' => '60'),       "miner's inch"),
        'MINERS_INCH_CO'              => array(array('' => '0.0442450734375', '/' => '60'),    "miner's inch"),
        'MINERS_INCH_ID'              => array(array('' => '0.0340687062', '/' => '60'),       "miner's inch"),
        'MINERS_INCH_WA'              => array(array('' => '0.0340687062', '/' => '60'),       "miner's inch"),
        'MINERS_INCH_NM'              => array(array('' => '0.0340687062', '/' => '60'),       "miner's inch"),
        'OUNCE_PER_DAY'               => array(array('' => '0.00454609', '/' => '13824000'),   'oz/day'),
        'OUNCE_PER_HOUR'              => array(array('' => '0.00454609', '/' => '576000'),     'oz/h'),
        'OUNCE_PER_MINUTE'            => array(array('' => '0.00454609', '/' => '9600'),       'oz/m'),
        'OUNCE_PER_SECOND'            => array(array('' => '0.00454609', '/' => '160'),        'oz/s'),
        'OUNCE_US_PER_DAY'            => array(array('' => '0.0037854118', '/' => '11059200'), 'oz/day'),
        'OUNCE_US_PER_HOUR'           => array(array('' => '0.0037854118', '/' => '460800'),   'oz/h'),
        'OUNCE_US_PER_MINUTE'         => array(array('' => '0.0037854118', '/' => '7680'),     'oz/m'),
        'OUNCE_US_PER_SECOND'         => array(array('' => '0.0037854118', '/' => '128'),      'oz/s'),
        'PETROGRAD_STANDARD_PER_DAY'  => array(array('' => '4.672279755', '/' => '86400'),     'petrograd standard/day'),
        'PETROGRAD_STANDARD_PER_HOUR' => array(array('' => '4.672279755', '/' => '3600'),      'petrograd standard/h'),
        'PETROGRAD_STANDARD_PER_MINUTE' => array(array('' => '4.672279755', '/' => '60'),      'petrograd standard/m'),
        'PETROGRAD_STANDARD_PER_SECOND' => array('4.672279755',                              'petrograd standard/s'),
        'STERE_PER_DAY'               => array(array('' => '1', '/' => '86400'),               'st/day'),
        'STERE_PER_HOUR'              => array(array('' => '1', '/' => '3600'),                'st/h'),
        'STERE_PER_MINUTE'            => array(array('' => '1', '/' => '60'),                  'st/m'),
        'STERE_PER_SECOND'            => array('1',                                          'st/s'),
        'THOUSAND_CUBIC_FOOT_PER_DAY' => array(array('' => '28.316847', '/' => '86400'),       'thousand ft³/day'),
        'THOUSAND_CUBIC_FOOT_PER_HOUR'   => array(array('' => '28.316847', '/' => '3600'),     'thousand ft³/h'),
        'THOUSAND_CUBIC_FOOT_PER_MINUTE' => array(array('' => '28.316847', '/' => '60'),       'thousand ft³/m'),
        'THOUSAND_CUBIC_FOOT_PER_SECOND' => array('28.316847',                               'thousand ft³/s'),
        'TRILLION_CUBIC_FOOT_PER_DAY'    => array(array('' => '28316847000', '/' => '86400'),  'trillion ft³/day'),
        'TRILLION_CUBIC_FOOT_PER_HOUR'   => array(array('' => '28316847000', '/' => '3600'),   'trillion ft³/h'),
        'TRILLION_CUBIC_FOOT_PER_MINUTE' => array(array('' => '28316847000', '/' => '60'),     'trillion ft³/m'),
        'TRILLION_CUBIC_FOOT_PER_'       => array('28316847000',                             'trillion ft³/s'),
        'STANDARD'                    => 'CUBIC_METER_PER_SECOND'
    );
}
