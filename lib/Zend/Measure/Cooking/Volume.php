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
 * Class for handling cooking volume conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Cooking_Volume
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Cooking_Volume extends Zend_Measure_Abstract
{
    const STANDARD = 'CUBIC_METER';

    const CAN_2POINT5       = 'CAN_2POINT5';
    const CAN_10            = 'CAN_10';
    const BARREL_WINE       = 'BARREL_WINE';
    const BARREL            = 'BARREL';
    const BARREL_US_DRY     = 'BARREL_US_DRY';
    const BARREL_US_FEDERAL = 'BARREL_US_FEDERAL';
    const BARREL_US         = 'BARREL_US';
    const BUCKET            = 'BUCKET';
    const BUCKET_US         = 'BUCKET_US';
    const BUSHEL            = 'BUSHEL';
    const BUSHEL_US         = 'BUSHEL_US';
    const CENTILITER        = 'CENTILITER';
    const COFFEE_SPOON      = 'COFFEE_SPOON';
    const CUBIC_CENTIMETER  = 'CUBIC_CENTIMETER';
    const CUBIC_DECIMETER   = 'CUBIC_DECIMETER';
    const CUBIC_FOOT        = 'CUBIC_FOOT';
    const CUBIC_INCH        = 'CUBIC_INCH';
    const CUBIC_METER       = 'CUBIC_METER';
    const CUBIC_MICROMETER  = 'CUBIC_MICROMETER';
    const CUBIC_MILLIMETER  = 'CUBIC_MILLIMETER';
    const CUP_CANADA        = 'CUP_CANADA';
    const CUP               = 'CUP';
    const CUP_US            = 'CUP_US';
    const DASH              = 'DASH';
    const DECILITER         = 'DECILITER';
    const DEKALITER         = 'DEKALITER';
    const DEMI              = 'DEMI';
    const DRAM              = 'DRAM';
    const DROP              = 'DROP';
    const FIFTH             = 'FIFTH';
    const GALLON            = 'GALLON';
    const GALLON_US_DRY     = 'GALLON_US_DRY';
    const GALLON_US         = 'GALLON_US';
    const GILL              = 'GILL';
    const GILL_US           = 'GILL_US';
    const HECTOLITER        = 'HECTOLITER';
    const HOGSHEAD          = 'HOGSHEAD';
    const HOGSHEAD_US       = 'HOGSHEAD_US';
    const JIGGER            = 'JIGGER';
    const KILOLITER         = 'KILOLITER';
    const LITER             = 'LITER';
    const MEASURE           = 'MEASURE';
    const MEGALITER         = 'MEGALITER';
    const MICROLITER        = 'MICROLITER';
    const MILLILITER        = 'MILLILITER';
    const MINIM             = 'MINIM';
    const MINIM_US          = 'MINIM_US';
    const OUNCE             = 'OUNCE';
    const OUNCE_US          = 'OUNCE_US';
    const PECK              = 'PECK';
    const PECK_US           = 'PECK_US';
    const PINCH             = 'PINCH';
    const PINT              = 'PINT';
    const PINT_US_DRY       = 'PINT_US_DRY';
    const PINT_US           = 'PINT_US';
    const PIPE              = 'PIPE';
    const PIPE_US           = 'PIPE_US';
    const PONY              = 'PONY';
    const QUART_GERMANY     = 'QUART_GERMANY';
    const QUART_ANCIENT     = 'QUART_ANCIENT';
    const QUART             = 'QUART';
    const QUART_US_DRY      = 'QUART_US_DRY';
    const QUART_US          = 'QUART_US';
    const SHOT              = 'SHOT';
    const TABLESPOON        = 'TABLESPOON';
    const TABLESPOON_UK     = 'TABLESPOON_UK';
    const TABLESPOON_US     = 'TABLESPOON_US';
    const TEASPOON          = 'TEASPOON';
    const TEASPOON_UK       = 'TEASPOON_UK';
    const TEASPOON_US       = 'TEASPOON_US';

    /**
     * Calculations for all cooking volume units
     *
     * @var array
     */
    protected $_units = array(
        'CAN_2POINT5'       => array(array('' => '0.0037854118', '/' => '16', '' => '3.5'), '2.5th can'),
        'CAN_10'            => array(array('' => '0.0037854118', '*' => '0.75'),          '10th can'),
        'BARREL_WINE'       => array('0.143201835',   'bbl'),
        'BARREL'            => array('0.16365924',    'bbl'),
        'BARREL_US_DRY'     => array(array('' => '26.7098656608', '/' => '231'), 'bbl'),
        'BARREL_US_FEDERAL' => array('0.1173477658',  'bbl'),
        'BARREL_US'         => array('0.1192404717',  'bbl'),
        'BUCKET'            => array('0.01818436',    'bucket'),
        'BUCKET_US'         => array('0.018927059',   'bucket'),
        'BUSHEL'            => array('0.03636872',    'bu'),
        'BUSHEL_US'         => array('0.03523907',    'bu'),
        'CENTILITER'        => array('0.00001',       'cl'),
        'COFFEE_SPOON'      => array(array('' => '0.0037854118', '/' => '1536'), 'coffee spoon'),
        'CUBIC_CENTIMETER'  => array('0.000001',      'cm³'),
        'CUBIC_DECIMETER'   => array('0.001',         'dm³'),
        'CUBIC_FOOT'        => array(array('' => '6.54119159', '/' => '231'),   'ft³'),
        'CUBIC_INCH'        => array(array('' => '0.0037854118', '/' => '231'), 'in³'),
        'CUBIC_METER'       => array('1',             'm³'),
        'CUBIC_MICROMETER'  => array('1.0e-18',       'µm³'),
        'CUBIC_MILLIMETER'  => array('1.0e-9',        'mm³'),
        'CUP_CANADA'        => array('0.0002273045',  'c'),
        'CUP'               => array('0.00025',       'c'),
        'CUP_US'            => array(array('' => '0.0037854118', '/' => '16'),   'c'),
        'DASH'              => array(array('' => '0.0037854118', '/' => '6144'), 'ds'),
        'DECILITER'         => array('0.0001',        'dl'),
        'DEKALITER'         => array('0.001',         'dal'),
        'DEMI'              => array('0.00025',       'demi'),
        'DRAM'              => array(array('' => '0.0037854118', '/' => '1024'),  'dr'),
        'DROP'              => array(array('' => '0.0037854118', '/' => '73728'), 'ggt'),
        'FIFTH'             => array('0.00075708236', 'fifth'),
        'GALLON'            => array('0.00454609',    'gal'),
        'GALLON_US_DRY'     => array('0.0044048838',  'gal'),
        'GALLON_US'         => array('0.0037854118',  'gal'),
        'GILL'              => array(array('' => '0.00454609', '/' => '32'),   'gi'),
        'GILL_US'           => array(array('' => '0.0037854118', '/' => '32'), 'gi'),
        'HECTOLITER'        => array('0.1',           'hl'),
        'HOGSHEAD'          => array('0.28640367',    'hhd'),
        'HOGSHEAD_US'       => array('0.2384809434',  'hhd'),
        'JIGGER'            => array(array('' => '0.0037854118', '/' => '128', '*' => '1.5'), 'jigger'),
        'KILOLITER'         => array('1',             'kl'),
        'LITER'             => array('0.001',         'l'),
        'MEASURE'           => array('0.0077',        'measure'),
        'MEGALITER'         => array('1000',          'Ml'),
        'MICROLITER'        => array('1.0e-9',        'µl'),
        'MILLILITER'        => array('0.000001',      'ml'),
        'MINIM'             => array(array('' => '0.00454609', '/' => '76800'),  'min'),
        'MINIM_US'          => array(array('' => '0.0037854118','/' => '61440'), 'min'),
        'OUNCE'             => array(array('' => '0.00454609', '/' => '160'),    'oz'),
        'OUNCE_US'          => array(array('' => '0.0037854118', '/' => '128'),  'oz'),
        'PECK'              => array('0.00909218',    'pk'),
        'PECK_US'           => array('0.0088097676',  'pk'),
        'PINCH'             => array(array('' => '0.0037854118', '/' => '12288'), 'pinch'),
        'PINT'              => array(array('' => '0.00454609', '/' => '8'),       'pt'),
        'PINT_US_DRY'       => array(array('' => '0.0044048838', '/' => '8'),     'pt'),
        'PINT_US'           => array(array('' => '0.0037854118', '/' => '8'),     'pt'),
        'PIPE'              => array('0.49097772',    'pipe'),
        'PIPE_US'           => array('0.4769618868',  'pipe'),
        'PONY'              => array(array('' => '0.0037854118', '/' => '128'), 'pony'),
        'QUART_GERMANY'     => array('0.00114504',    'qt'),
        'QUART_ANCIENT'     => array('0.00108',       'qt'),
        'QUART'             => array(array('' => '0.00454609', '/' => '4'),     'qt'),
        'QUART_US_DRY'      => array(array('' => '0.0044048838', '/' => '4'),   'qt'),
        'QUART_US'          => array(array('' => '0.0037854118', '/' => '4'),   'qt'),
        'SHOT'              => array(array('' => '0.0037854118', '/' => '128'), 'shot'),
        'TABLESPOON'        => array('0.000015',      'tbsp'),
        'TABLESPOON_UK'     => array(array('' => '0.00454609', '/' => '320'),   'tbsp'),
        'TABLESPOON_US'     => array(array('' => '0.0037854118', '/' => '256'), 'tbsp'),
        'TEASPOON'          => array('0.000005',      'tsp'),
        'TEASPOON_UK'       => array(array('' => '0.00454609', '/' => '1280'),  'tsp'),
        'TEASPOON_US'       => array(array('' => '0.0037854118', '/' => '768'), 'tsp'),
        'STANDARD'          => 'CUBIC_METER'
    );
}
