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
 * Class for handling force conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Force
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Force extends Zend_Measure_Abstract
{
    const STANDARD = 'NEWTON';

    const ATTONEWTON      = 'ATTONEWTON';
    const CENTINEWTON     = 'CENTINEWTON';
    const DECIGRAM_FORCE  = 'DECIGRAM_FORCE';
    const DECINEWTON      = 'DECINEWTON';
    const DEKAGRAM_FORCE  = 'DEKAGRAM_FORCE';
    const DEKANEWTON      = 'DEKANEWTON';
    const DYNE            = 'DYNE';
    const EXANEWTON       = 'EXANEWTON';
    const FEMTONEWTON     = 'FEMTONEWTON';
    const GIGANEWTON      = 'GIGANEWTON';
    const GRAM_FORCE      = 'GRAM_FORCE';
    const HECTONEWTON     = 'HECTONEWTON';
    const JOULE_PER_METER = 'JOULE_PER_METER';
    const KILOGRAM_FORCE  = 'KILOGRAM_FORCE';
    const KILONEWTON      = 'KILONEWTON';
    const KILOPOND        = 'KILOPOND';
    const KIP             = 'KIP';
    const MEGANEWTON      = 'MEGANEWTON';
    const MEGAPOND        = 'MEGAPOND';
    const MICRONEWTON     = 'MICRONEWTON';
    const MILLINEWTON     = 'MILLINEWTON';
    const NANONEWTON      = 'NANONEWTON';
    const NEWTON          = 'NEWTON';
    const OUNCE_FORCE     = 'OUNCE_FORCE';
    const PETANEWTON      = 'PETANEWTON';
    const PICONEWTON      = 'PICONEWTON';
    const POND            = 'POND';
    const POUND_FORCE     = 'POUND_FORCE';
    const POUNDAL         = 'POUNDAL';
    const STHENE          = 'STHENE';
    const TERANEWTON      = 'TERANEWTON';
    const TON_FORCE_LONG  = 'TON_FORCE_LONG';
    const TON_FORCE       = 'TON_FORCE';
    const TON_FORCE_SHORT = 'TON_FORCE_SHORT';
    const YOCTONEWTON     = 'YOCTONEWTON';
    const YOTTANEWTON     = 'YOTTANEWTON';
    const ZEPTONEWTON     = 'ZEPTONEWTON';
    const ZETTANEWTON     = 'ZETTANEWTON';

    /**
     * Calculations for all force units
     *
     * @var array
     */
    protected $_units = array(
        'ATTONEWTON'      => array('1.0e-18',     'aN'),
        'CENTINEWTON'     => array('0.01',        'cN'),
        'DECIGRAM_FORCE'  => array('0.000980665', 'dgf'),
        'DECINEWTON'      => array('0.1',         'dN'),
        'DEKAGRAM_FORCE'  => array('0.0980665',   'dagf'),
        'DEKANEWTON'      => array('10',          'daN'),
        'DYNE'            => array('0.00001',     'dyn'),
        'EXANEWTON'       => array('1.0e+18',     'EN'),
        'FEMTONEWTON'     => array('1.0e-15',     'fN'),
        'GIGANEWTON'      => array('1.0e+9',      'GN'),
        'GRAM_FORCE'      => array('0.00980665',  'gf'),
        'HECTONEWTON'     => array('100',         'hN'),
        'JOULE_PER_METER' => array('1',           'J/m'),
        'KILOGRAM_FORCE'  => array('9.80665',     'kgf'),
        'KILONEWTON'      => array('1000',        'kN'),
        'KILOPOND'        => array('9.80665',     'kp'),
        'KIP'             => array('4448.2216',   'kip'),
        'MEGANEWTON'      => array('1000000',     'Mp'),
        'MEGAPOND'        => array('9806.65',     'MN'),
        'MICRONEWTON'     => array('0.000001',    'µN'),
        'MILLINEWTON'     => array('0.001',       'mN'),
        'NANONEWTON'      => array('0.000000001', 'nN'),
        'NEWTON'          => array('1',           'N'),
        'OUNCE_FORCE'     => array('0.27801385',  'ozf'),
        'PETANEWTON'      => array('1.0e+15',     'PN'),
        'PICONEWTON'      => array('1.0e-12',     'pN'),
        'POND'            => array('0.00980665',  'pond'),
        'POUND_FORCE'     => array('4.4482216',   'lbf'),
        'POUNDAL'         => array('0.13825495',  'pdl'),
        'STHENE'          => array('1000',        'sn'),
        'TERANEWTON'      => array('1.0e+12',     'TN'),
        'TON_FORCE_LONG'  => array('9964.016384', 'tnf'),
        'TON_FORCE'       => array('9806.65',     'tnf'),
        'TON_FORCE_SHORT' => array('8896.4432',   'tnf'),
        'YOCTONEWTON'     => array('1.0e-24',     'yN'),
        'YOTTANEWTON'     => array('1.0e+24',     'YN'),
        'ZEPTONEWTON'     => array('1.0e-21',     'zN'),
        'ZETTANEWTON'     => array('1.0e+21',     'ZN'),
        'STANDARD'        => 'NEWTON'
    );
}
