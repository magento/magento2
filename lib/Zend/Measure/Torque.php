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
 * @version   $Id: Torque.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Implement needed classes
 */
#require_once 'Zend/Measure/Abstract.php';
#require_once 'Zend/Locale.php';

/**
 * Class for handling torque conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Torque
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Torque extends Zend_Measure_Abstract
{
    const STANDARD = 'NEWTON_METER';

    const DYNE_CENTIMETER     = 'DYNE_CENTIMETER';
    const GRAM_CENTIMETER     = 'GRAM_CENTIMETER';
    const KILOGRAM_CENTIMETER = 'KILOGRAM_CENTIMETER';
    const KILOGRAM_METER      = 'KILOGRAM_METER';
    const KILONEWTON_METER    = 'KILONEWTON_METER';
    const KILOPOND_METER      = 'KILOPOND_METER';
    const MEGANEWTON_METER    = 'MEGANEWTON_METER';
    const MICRONEWTON_METER   = 'MICRONEWTON_METER';
    const MILLINEWTON_METER   = 'MILLINEWTON_METER';
    const NEWTON_CENTIMETER   = 'NEWTON_CENTIMETER';
    const NEWTON_METER        = 'NEWTON_METER';
    const OUNCE_FOOT          = 'OUNCE_FOOT';
    const OUNCE_INCH          = 'OUNCE_INCH';
    const POUND_FOOT          = 'POUND_FOOT';
    const POUNDAL_FOOT        = 'POUNDAL_FOOT';
    const POUND_INCH          = 'POUND_INCH';

    /**
     * Calculations for all torque units
     *
     * @var array
     */
    protected $_units = array(
        'DYNE_CENTIMETER'     => array('0.0000001',          'dyncm'),
        'GRAM_CENTIMETER'     => array('0.0000980665',       'gcm'),
        'KILOGRAM_CENTIMETER' => array('0.0980665',          'kgcm'),
        'KILOGRAM_METER'      => array('9.80665',            'kgm'),
        'KILONEWTON_METER'    => array('1000',               'kNm'),
        'KILOPOND_METER'      => array('9.80665',            'kpm'),
        'MEGANEWTON_METER'    => array('1000000',            'MNm'),
        'MICRONEWTON_METER'   => array('0.000001',           'µNm'),
        'MILLINEWTON_METER'   => array('0.001',              'mNm'),
        'NEWTON_CENTIMETER'   => array('0.01',               'Ncm'),
        'NEWTON_METER'        => array('1',                  'Nm'),
        'OUNCE_FOOT'          => array('0.084738622',        'ozft'),
        'OUNCE_INCH'          => array(array('' => '0.084738622', '/' => '12'), 'ozin'),
        'POUND_FOOT'          => array(array('' => '0.084738622', '*' => '16'), 'lbft'),
        'POUNDAL_FOOT'        => array('0.0421401099752144', 'plft'),
        'POUND_INCH'          => array(array('' => '0.084738622', '/' => '12', '*' => '16'), 'lbin'),
        'STANDARD'            => 'NEWTON_METER'
    );
}
