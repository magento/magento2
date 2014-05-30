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
 * @version   $Id: Current.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Implement needed classes
 */
#require_once 'Zend/Measure/Abstract.php';
#require_once 'Zend/Locale.php';

/**
 * Class for handling current conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Current
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Current extends Zend_Measure_Abstract
{
    const STANDARD = 'AMPERE';

    const ABAMPERE             = 'ABAMPERE';
    const AMPERE               = 'AMPERE';
    const BIOT                 = 'BIOT';
    const CENTIAMPERE          = 'CENTIAMPERE';
    const COULOMB_PER_SECOND   = 'COULOMB_PER_SECOND';
    const DECIAMPERE           = 'DECIAMPERE';
    const DEKAAMPERE           = 'DEKAAMPERE';
    const ELECTROMAGNETIC_UNIT = 'ELECTROMAGNATIC_UNIT';
    const ELECTROSTATIC_UNIT   = 'ELECTROSTATIC_UNIT';
    const FRANCLIN_PER_SECOND  = 'FRANCLIN_PER_SECOND';
    const GAUSSIAN             = 'GAUSSIAN';
    const GIGAAMPERE           = 'GIGAAMPERE';
    const GILBERT              = 'GILBERT';
    const HECTOAMPERE          = 'HECTOAMPERE';
    const KILOAMPERE           = 'KILOAMPERE';
    const MEGAAMPERE           = 'MEGAAMPERE';
    const MICROAMPERE          = 'MICROAMPERE';
    const MILLIAMPERE          = 'MILLIAMPERE';
    const NANOAMPERE           = 'NANOAMPERE';
    const PICOAMPERE           = 'PICOAMPERE';
    const SIEMENS_VOLT         = 'SIEMENS_VOLT';
    const STATAMPERE           = 'STATAMPERE';
    const TERAAMPERE           = 'TERAAMPERE';
    const VOLT_PER_OHM         = 'VOLT_PER_OHM';
    const WATT_PER_VOLT        = 'WATT_PER_VOLT';
    const WEBER_PER_HENRY      = 'WEBER_PER_HENRY';

    /**
     * Calculations for all current units
     *
     * @var array
     */
    protected $_units = array(
        'ABAMPERE'             => array('10',           'abampere'),
        'AMPERE'               => array('1',            'A'),
        'BIOT'                 => array('10',           'Bi'),
        'CENTIAMPERE'          => array('0.01',         'cA'),
        'COULOMB_PER_SECOND'   => array('1',            'C/s'),
        'DECIAMPERE'           => array('0.1',          'dA'),
        'DEKAAMPERE'           => array('10',           'daA'),
        'ELECTROMAGNATIC_UNIT' => array('10',           'current emu'),
        'ELECTROSTATIC_UNIT'   => array('3.335641e-10', 'current esu'),
        'FRANCLIN_PER_SECOND'  => array('3.335641e-10', 'Fr/s'),
        'GAUSSIAN'             => array('3.335641e-10', 'G current'),
        'GIGAAMPERE'           => array('1.0e+9',       'GA'),
        'GILBERT'              => array('0.79577472',   'Gi'),
        'HECTOAMPERE'          => array('100',          'hA'),
        'KILOAMPERE'           => array('1000',         'kA'),
        'MEGAAMPERE'           => array('1000000',      'MA') ,
        'MICROAMPERE'          => array('0.000001',     'µA'),
        'MILLIAMPERE'          => array('0.001',        'mA'),
        'NANOAMPERE'           => array('1.0e-9',       'nA'),
        'PICOAMPERE'           => array('1.0e-12',      'pA'),
        'SIEMENS_VOLT'         => array('1',            'SV'),
        'STATAMPERE'           => array('3.335641e-10', 'statampere'),
        'TERAAMPERE'           => array('1.0e+12',      'TA'),
        'VOLT_PER_OHM'         => array('1',            'V/Ohm'),
        'WATT_PER_VOLT'        => array('1',            'W/V'),
        'WEBER_PER_HENRY'      => array('1',            'Wb/H'),
        'STANDARD'             => 'AMPERE'
    );
}
