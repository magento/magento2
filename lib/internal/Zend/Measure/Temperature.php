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
 * @version   $Id: Temperature.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Implement needed classes
 */
#require_once 'Zend/Measure/Abstract.php';
#require_once 'Zend/Locale.php';

/**
 * Class for handling temperature conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Temperature
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Temperature extends Zend_Measure_Abstract
{
    const STANDARD = 'KELVIN';

    const CELSIUS    = 'CELSIUS';
    const FAHRENHEIT = 'FAHRENHEIT';
    const RANKINE    = 'RANKINE';
    const REAUMUR    = 'REAUMUR';
    const KELVIN     = 'KELVIN';

    /**
     * Calculations for all temperature units
     *
     * @var array
     */
    protected $_units = array(
        'CELSIUS'    => array(array('' => '1', '+' => '273.15'),'°C'),
        'FAHRENHEIT' => array(array('' => '1', '-' => '32', '/' => '1.8', '+' => '273.15'),'°F'),
        'RANKINE'    => array(array('' => '1', '/' => '1.8'),'°R'),
        'REAUMUR'    => array(array('' => '1', '*' => '1.25', '+' => '273.15'),'°r'),
        'KELVIN'     => array(1,'°K'),
        'STANDARD'   => 'KELVIN'
    );
}
