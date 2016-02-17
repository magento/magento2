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
 * Class for handling illumination conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Illumination
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Illumination extends Zend_Measure_Abstract
{
    const STANDARD = 'LUX';

    const FOOTCANDLE                  = 'FOOTCANDLE';
    const KILOLUX                     = 'KILOLUX';
    const LUMEN_PER_SQUARE_CENTIMETER = 'LUMEN_PER_SQUARE_CENTIMETER';
    const LUMEN_PER_SQUARE_FOOT       = 'LUMEN_PER_SQUARE_FOOT';
    const LUMEN_PER_SQUARE_INCH       = 'LUMEN_PER_SQUARE_INCH';
    const LUMEN_PER_SQUARE_METER      = 'LUMEN_PER_SQUARE_METER';
    const LUX                         = 'LUX';
    const METERCANDLE                 = 'METERCANDLE';
    const MILLIPHOT                   = 'MILLIPHOT';
    const NOX                         = 'NOX';
    const PHOT                        = 'PHOT';

    /**
     * Calculations for all illumination units
     *
     * @var array
     */
    protected $_units = array(
        'FOOTCANDLE'              => array('10.7639104',   'fc'),
        'KILOLUX'                 => array('1000',         'klx'),
        'LUMEN_PER_SQUARE_CENTIMETER' => array('10000',    'lm/cm²'),
        'LUMEN_PER_SQUARE_FOOT'   => array('10.7639104',   'lm/ft²'),
        'LUMEN_PER_SQUARE_INCH'   => array('1550.0030976', 'lm/in²'),
        'LUMEN_PER_SQUARE_METER'  => array('1',            'lm/m²'),
        'LUX'                     => array('1',            'lx'),
        'METERCANDLE'             => array('1',            'metercandle'),
        'MILLIPHOT'               => array('10',           'mph'),
        'NOX'                     => array('0.001',        'nox'),
        'PHOT'                    => array('10000',        'ph'),
        'STANDARD'                => 'LUX'
    );
}
