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
 * @version   $Id: Weight.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Implement needed classes
 */
#require_once 'Zend/Measure/Abstract.php';
#require_once 'Zend/Locale.php';

/**
 * Class for handling cooking weight conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Cooking_Weight
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Cooking_Weight extends Zend_Measure_Abstract
{
    const STANDARD = 'GRAM';

    const HALF_STICK    = 'HALF_STICK';
    const STICK         = 'STICK';
    const CUP           = 'CUP';
    const GRAM          = 'GRAM';
    const OUNCE         = 'OUNCE';
    const POUND         = 'POUND';
    const TEASPOON      = 'TEASPOON';
    const TEASPOON_US   = 'TEASPOON_US';
    const TABLESPOON    = 'TABLESPOON';
    const TABLESPOON_US = 'TABLESPOON_US';

    /**
     * Calculations for all cooking weight units
     *
     * @var array
     */
    protected $_units = array(
        'HALF_STICK'    => array(array('' => '453.59237', '/' => '8'),                    'half stk'),
        'STICK'         => array(array('' => '453.59237', '/' => '4'),                    'stk'),
        'CUP'           => array(array('' => '453.59237', '/' => '2'),                    'c'),
        'GRAM'          => array('1',                                                   'g'),
        'OUNCE'         => array(array('' => '453.59237', '/' => '16'),                   'oz'),
        'POUND'         => array('453.59237',                                           'lb'),
        'TEASPOON'      => array(array('' => '1.2503332', '' => '453.59237', '/' => '128'), 'tsp'),
        'TEASPOON_US'   => array(array('' => '453.59237', '/' => '96'),                   'tsp'),
        'TABLESPOON'    => array(array('' => '1.2503332', '' => '453.59237', '/' => '32'),  'tbsp'),
        'TABLESPOON_US' => array(array('' => '453.59237', '/' => '32'),                   'tbsp'),
        'STANDARD'      => 'GRAM'
    );
}
