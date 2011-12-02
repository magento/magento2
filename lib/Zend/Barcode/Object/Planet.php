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
 * @category   Zend
 * @package    Zend_Barcode
 * @subpackage Object
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Planet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Barcode_Object_Postnet
 */
#require_once 'Zend/Barcode/Object/Postnet.php';

/**
 * @see Zend_Validate_Barcode
 */
#require_once 'Zend/Validate/Barcode.php';

/**
 * Class for generate Planet barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Planet extends Zend_Barcode_Object_Postnet
{

    /**
     * Coding map
     * - 0 = half bar
     * - 1 = complete bar
     * @var array
     */
    protected $_codingMap = array(
        0 => "00111",
        1 => "11100",
        2 => "11010",
        3 => "11001",
        4 => "10110",
        5 => "10101",
        6 => "10011",
        7 => "01110",
        8 => "01101",
        9 => "01011"
    );
}